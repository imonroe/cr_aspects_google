<?php

namespace imonroe\cr_aspects_google\Http\Controllers;

use Auth;
use Socialite;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

use Google_Client;
use Google_Service_Tasks;
use Google_Service_Tasks_Task;
use Google_Service_Tasks_TaskLists;
use Google_Service_Tasks_TaskList;
use Google_Service_Tasks_Tasklists_Resource;
use Google_Service_Calendar;
use Google_Http_Request;
use Google_Service_Drive;

use imonroe\ana\Ana;
use imonroe\cr_aspects_google\GoogleContactsAPIResultsAspect;
use imonroe\cr_aspects_google\GoogleContactDataAspect;
use imonroe\cr_aspects_google\GoogleTasksListAspect;
use imonroe\cr_aspects_google\GoogleCalendarAspect;

use Carbon\Carbon;
use Snoopy\Snoopy;

class GoogleController extends Controller{
	protected $google_config;
	protected $client;
	protected $user;
	
	function __construct(){
		//$this->middleware(function ($request, $next) {
		//	$this->user = auth()->user();
		//	return $next($request);
		// });
		// $this->build_client();
		// dd('Middleware break', $this);
		$app_config = app('config')->get('services');
		if ( !empty($app_config['google']) ){
			$this->google_config = $app_config['google'];
			$scopes = [
				"https://www.googleapis.com/auth/drive",
				"https://www.googleapis.com/auth/calendar",
				"https://www.googleapis.com/auth/tasks",
				"https://www.googleapis.com/auth/userinfo.email",
				"https://www.googleapis.com/auth/userinfo.profile",
				"https://www.google.com/m8/feeds/",
			];
			$client = new Google_Client();
			$client->setApplicationName( $this->google_config['application_name'] );
			$client->setAuthConfig( $this->google_config['auth_config_file'] );
			$client->addScope($scopes);
			$client->setClientId( $this->google_config['client_id'] );
			$client->setClientSecret( $this->google_config['client_secret'] );
			$client->setDeveloperKey( $this->google_config['public_api_key'] );
			$client->setAccessType("offline");
			$client->setApprovalPrompt("force");  // Disable after debugging. Forces a refresh token.
			$client->setRedirectUri( env('APP_URL') . '/auth/google/callback' );
			$this->client = $client;
		} else {
			throw \Exception('No Google Configuration found.');
		}
	}

	public function get_client(){
		return $this->client;
	}

	public function build_client(){
		// session([ 'oauth_redirect_request' => Ana::current_page_url() ]);
		// Session::put('oauth_redirect_request', Ana::current_page_url());
		// We need a user object.
		if (Auth::check()){
			$this->user = Auth::user();
		} else {
			throw \Exception('User not logged in.');
		}
		 
		$app_config = app('config')->get('services');
		if ( !empty( $this->client ) ){
			if ( !empty($this->user->google_token) ) {
				// We have a token in the database.
				$google_client_token = json_decode( $this->user->google_token, true );
			} else {
				// There is no token in the database.
				$auth_url = $this->client->createAuthUrl();
				Redirect::to($auth_url)->send();
			}
			$this->client->setAccessToken(json_encode($google_client_token));
			if($this->client->isAccessTokenExpired()){
				$this->client->setAccessType("refresh_token");
				$this->client->refreshToken($google_client_token['refresh_token']);
				$new_token = $this->client->getAccessToken();
				$this->user->google_token = json_encode($new_token);
				$this->user->save();
			}
		} else {
			throw \Exception('No Google client available.');
		}	
	}

	// Tasks stuff.
	public function new_task_list(Request $request){
		$this->build_client();
		$input = $request->all();
		$tasks_service = new Google_Service_Tasks($this->client);
		$tasklists = $tasks_service->tasklists;
		$new_list = new Google_Service_Tasks_TaskList;
		$new_list->setTitle( $input['task_list_name'] );
		return json_encode( $tasklists->insert($new_list) );
		//die();
	}

	public function task_list($task_list_id='@default'){
		/*
			This function returns an object that looks like the following:
			Useful: $object->items[0]['title']	

			Google_Service_Tasks_Tasks {#274 ▼
				#collection_key: "items"
				+etag: ""ZPF2pw17LedTHeJNTnTTe4cmlp4/MTE2MTMxNDIyNw""
				#itemsType: "Google_Service_Tasks_Task"
				#itemsDataType: "array"
				+kind: "tasks#tasks"
				+nextPageToken: null
				#internal_gapi_mappings: []
				#modelData: array:1 [▼
				"items" => array:7 [▼
					0 => array:9 [▼
					"kind" => "tasks#task"
					"id" => "MDE0MTE1MjY1MjcwNzYwNDY5Nzg6MDo1NDIwNzM0NTc"
					"etag" => ""ZPF2pw17LedTHeJNTnTTe4cmlp4/MTAyNjY0NjcwOA""
					"title" => "Laundry"
					"updated" => "2017-05-26T14:44:22.000Z"
					"selfLink" => "https://www.googleapis.com/tasks/v1/lists/..."
					"position" => "00000000000002796203"
					"status" => "needsAction"
					"due" => "2017-05-27T00:00:00.000Z"
					]
					1 => array:9 [▶]
				]
				]
				#processed: []
			}
		*/
		$this->build_client();	
		$tomorrow = strtotime('+1 day');
		$tomorrow_timestamp = date(DATE_RFC3339, $tomorrow);
		//$today_timestamp = date(DATE_RFC3339, strtotime('today 11:59PM'));
		$todo_service = new Google_Service_Tasks($this->client);
		$optParams = array(
			'maxResults' => 10,
		);
		$all_task_lists = $todo_service->tasklists->listTasklists($optParams);
		$list_params = array(
			'dueMax' => $tomorrow_timestamp,
			'showCompleted' => false					
		);
		$todo_list = $todo_service->tasks->listTasks($task_list_id, $list_params);
		return $todo_list;
	}

	public function display_task_list($task_list_id='@default'){
		$this->build_client();
		$tomorrow = strtotime('+1 day');
		$tomorrow_timestamp = date(DATE_RFC3339, $tomorrow);
		$tasks_service = new Google_Service_Tasks($this->client);
		$list_params = array(
			'dueMax' => $tomorrow_timestamp,
			'showCompleted' => false					
		);
		$task_list = $tasks_service->tasks->listTasks($task_list_id, $list_params);
		return json_encode($task_list);
	}
	
	public function get_all_task_lists(){
		$this->build_client();
		$tasksService = new Google_Service_Tasks($this->client);
 		return json_encode($tasksService->tasklists->listTasklists());
	}

	public function new_task(Request $request){
		$this->build_client();
		$today_timestamp = date(DATE_RFC3339, strtotime('today 11:59PM'));
		$list_id = !empty($request->input('task_list')) ? $request->input('task_list') : '@default';
		$todo_service = new Google_Service_Tasks($this->client);
		$task = new Google_Service_Tasks_Task();
		$task->setTitle($request->input('new_task_title'));
		$task->setDue($today_timestamp);
		$result = $todo_service->tasks->insert($list_id, $task);
		echo('Got back: '.$result->getId());
	}

	public function edit_task(Request $request){
		$this->build_client();
		$today_timestamp = date(DATE_RFC3339, strtotime('today 11:59PM'));

	}

	public function complete_task(Request $request){
		$today_timestamp = date(DATE_RFC3339, strtotime('today 11:59PM'));
		$list_id = !empty($request->input('list_id')) ? $request->input('list_id') : '@default';
		$todo_service = new Google_Service_Tasks($this->client);
		$task = $todo_service->tasks->get($list_id, $request->input('task_id') );
		$task->setStatus('completed');
		$result = $todo_service->tasks->update($list_id, $task->getId(), $task);
	}

	public function remove_task(Request $request){
		$today_timestamp = date(DATE_RFC3339, strtotime('today 11:59PM'));

	}





	// Calendar stuff.
	public function get_calendar($calendar_id='primary'){
		$calendar_service = new Google_Service_Calendar($this->client);
		$optParams = array(
			'timeMin' => Ana::google_datetime(strtotime(Carbon::now()->subMinutes(60))),
			'orderBy' => 'startTime',
			'singleEvents' => true,
			'timeMax' =>Ana::google_datetime(strtotime('tomorrow 3:00AM')),
		);
		$event_list = $calendar_service->events->listEvents($calendar_id, $optParams);
		$output = '';
		$output .= '<ul id="calendar_agenda">';
		foreach($event_list->items as $event){
			$output .=  '<li>'.$event['summary'].' - '.Ana::standard_date_format(strtotime($event['start']['dateTime'])).'</li>';
		}
		$output .=  '</ul>';
		return $output;
	}

	public function edit_calendar(Request $request){
		switch($request->input('action')){
			case "new_appointment":
				$calendar_service = new Google_Service_Calendar($this->client);
				$result = $calendar_service->events->quickAdd( 'primary', $request->input('new_appointment_txt') );
				echo('Got back: '.$result->getId());
				break;
		}
	}

	// Search stuff
	public function google_search($query){
		$cse_link = 'https://www.googleapis.com/customsearch/v1';
		$cse_link .= '?key='.env('GOOGLE_CUSTOM_SEARCH_API_KEY');
		$cse_link .= '&q='.urlencode($query);
		$cse_link .= '&cx='.env('GOOGLE_CUSTOM_SEARCH_CZ');
		$cse_link .= '&alt=json';
		$fetcher = new Snoopy;
		$fetcher->fetch($cse_link);
		$results = $fetcher->results;
		$results_array = json_decode($results, true);
		return $results_array;
	}

	// Contacts stuff
	public function get_contacts(){
		// returns JSON formatted contact list from Google.
		$request_url = 'https://www.google.com/m8/feeds/contacts/'.urlencode(env('APP_PRIMARY_USER_EMAIL')).'/full?max-results=2000&alt=json';
		return $this->get_authenticated_url($request_url);
	}

	// General functions
	public function get_authenticated_url($request_url){
		$token = $this->client->getAccessToken();
		$header = array();
		$header[] = "Content-type: application/atom+xml"; 
		$header[] = 'Authorization: OAuth '.$token['access_token'];
		$header[] = 'GData-Version: 3.0';

		$curl = curl_init();
 		curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($curl, CURLOPT_HTTPGET, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($curl,CURLOPT_URL, $request_url);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, TRUE);
		$api_response = trim(curl_exec($curl));
		$curl_error = curl_error($curl);
		curl_close($curl);
		return $api_response;
	}

	// Maps stuff
	public static function get_static_map($address){
		// returns a hyperlinked map to a given address.
		$static_maps_endpoint = 'https://maps.googleapis.com/maps/api/staticmap?';
		$api_key = env('GOOGLE_STATIC_MAPS_API_KEY');
		$parameters = array(
			'center' => urlencode($address),
			'size' => '250x250',
			'scale' => '1',
			'format' => 'png',
			'markers' => 'color:blue%7C'.urlencode($address),
			'mapType' => 'roadmap',  // {roadmap, satellite, hybrid, or terrain}
			'zoom' => '15',  //{ 0 is fully zoomed out, 20 is fully zoomed in}
			'key' => $api_key
		);
		foreach ($parameters as $key => $value){
			$static_maps_endpoint .= $key . '=' . $value . '&';
		}
		$hyperlink = 'https://www.google.com/maps/place/'. urlencode($address);
		$output = '<a href="'.$hyperlink.'" target="_blank"><img src="'.$static_maps_endpoint.'" ></a>';
		return $output;
	}

	// Drive stuff
	public function search_drive($query=''){
		$excluded_directory = '';
		$output = array();
		$service = new Google_Service_Drive($this->client);
		$pageToken = null;
		do {
			$response = $service->files->listFiles(array(
				'q' => "name contains '".$query."' and (trashed = false)",
				'spaces' => 'drive',
				'pageToken' => $pageToken,
				'fields' => 'nextPageToken, files(id, name, webViewLink, parents)',
			));

			foreach ($response->files as $file) {
				//dd($file);
				$output[$file->getWebViewLink()] = $file->getName() . ' parents: '. var_export($file->getParents(), true); 
			}
		} while ($pageToken != null);
		return $output;
	}


}

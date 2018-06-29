<?php

namespace imonroe\cr_aspects_google\Http\Controllers;

use Auth;
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
use imonroe\crps\SubjectType;
use imonroe\cr_aspects_google\GoogleContactsAPIResultsAspect;
use imonroe\cr_aspects_google\GoogleContactDataAspect;
use imonroe\cr_aspects_google\GoogleTasksListAspect;
use imonroe\cr_aspects_google\GoogleCalendarAspect;

use Carbon\Carbon;
use Snoopy\Snoopy;

class GoogleController extends Controller
{
    protected $client;
    protected $user;
    protected $auth_url;
    
    function __construct()
    {
        $app_config = app('config')->get('services');
        $google_config = $app_config['google'];
        if (!empty($google_config)) {
            $gc = resolve('Google_Client');
            $scopes = [
                "https://www.googleapis.com/auth/drive",
                "https://www.googleapis.com/auth/calendar",
                "https://www.googleapis.com/auth/tasks",
                "https://www.googleapis.com/auth/userinfo.email",
                "https://www.googleapis.com/auth/userinfo.profile",
                "https://www.google.com/m8/feeds/",
            ];
            $gc->addScope($scopes);
            $gc->setApprovalPrompt("force");
            $gc->setAccessType("offline");
            $gc->setDeveloperKey($google_config['public_api_key']);
            $gc->setApplicationName($google_config['application_name']);
            $this->auth_url = $gc->createAuthUrl();
            $this->client = $gc;
        } else {
            throw new \Exception('cr_aspects_google: Could not determine config. Is it set up right?');
        }

        $this->middleware(function ($request, $next) use ($gc) {
            $google_client_token = $this->auth();
            $gc->setAccessToken(json_encode($google_client_token));
            if ($gc->isAccessTokenExpired()) {
                $gc->setAccessType("refresh_token");
                $gc->refreshToken($google_client_token['refresh_token']);
                $new_token = $gc->getAccessToken();
                $user->google_token = json_encode($new_token);
                $user->save();
            }
            $this->client = $gc;
            return $next($request);
        });
    }

    public function get_client()
    {
        return $this->client;
    }

    public function auth()
    {
        $this->user = Auth::user();
        $gct = session('google_token', $this->user->google_token);
        $google_client_token = json_decode($gct, true);
        if (empty($google_client_token)) {
            header('Location: '.$this->auth_url);
            die();
        }
        return $google_client_token;
    }

    // Tasks stuff.
    public function new_task_list(Request $request)
    {
        $input = $request->all();
        $tasks_service = new Google_Service_Tasks($this->client);
        $tasklists = $tasks_service->tasklists;
        $new_list = new Google_Service_Tasks_TaskList;
        $new_list->setTitle($input['task_list_name']);
        return json_encode($tasklists->insert($new_list));
    }

    public function task_list($task_list_id = '@default')
    {
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

    public function display_task_list($task_list_id = '@default')
    {
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
    
    public function get_all_task_lists()
    {
        $tasksService = new Google_Service_Tasks($this->client);
        return json_encode($tasksService->tasklists->listTasklists());
    }

    public function new_task(Request $request)
    {
        $input = $request->all();
        $today_timestamp = date(DATE_RFC3339, strtotime('today 11:59PM'));
        $list_id = $request->input('task_list', '@default');
        $todo_service = new Google_Service_Tasks($this->client);
        $task = new Google_Service_Tasks_Task();
        $task->setTitle($request->input('new_task_title', 'empty'));
        $task->setDue($today_timestamp);
        $result = $todo_service->tasks->insert($list_id, $task);
        echo('Got back: '.$result->getId());
    }

    public function edit_task(Request $request)
    {
        $today_timestamp = date(DATE_RFC3339, strtotime('today 11:59PM'));
    }

    public function complete_task(Request $request)
    {
        $input = $request->json()->all();
        $today_timestamp = date(DATE_RFC3339, strtotime('today 11:59PM'));
        $list_id = !empty($input['list_id']) ? $input['list_id'] : '@default';
        $todo_service = new Google_Service_Tasks($this->client);
        $gtask = $todo_service->tasks->get($list_id, $input['task_id']);
        $gtask->setStatus('completed');
        $result = $todo_service->tasks->update($list_id, $input['task_id'], $gtask);
        echo( 'Got back: '.json_encode($result) );
    }

    public function remove_task(Request $request)
    {
        $today_timestamp = date(DATE_RFC3339, strtotime('today 11:59PM'));
    }


    // Calendar stuff.
    public function list_calendars(Request $request)
    {
        $calendar_service = new Google_Service_Calendar($this->client);
        $calendar_list = $calendar_service->calendarList->listCalendarList();
        return json_encode($calendar_list);
    }
    public function create_calendar(Request $request)
    {
    }
    public function get_calendar(Request $request)
    {
        $input = $request->all();
        $calendar_id = ( !empty($input['calendar_id']) ) ? $input['calendar_id'] : 'primary';
        $start_time = ( !empty($input['start_date']) ) ? Ana::google_datetime(strtotime($input['start_date'])) : Ana::google_datetime(strtotime(Carbon::now()->subMinutes(60)));
        $end_time = ( !empty($input['end_date']) ) ? Ana::google_datetime(strtotime($input['end_date']))  : Ana::google_datetime(strtotime('tomorrow 3:00AM'));
        $calendar_service = new Google_Service_Calendar($this->client);
        $optParams = array(
            'timeMin' => $start_time,
            'orderBy' => 'startTime',
            'singleEvents' => true,
            'timeMax' => $end_time,
        );
        $event_list = $calendar_service->events->listEvents($calendar_id, $optParams);
        return json_encode($event_list);
    }

    public function get_event(Request $request)
    {
    }
    public function create_event(Request $request)
    {
        $input = $request->json()->all();
        $calendar_id = (!empty($input['calendar_id'])) ? $input['calendar_id'] : 'primary';
        $new_event_name = $input['new_event_name'];
        $calendar_service = new Google_Service_Calendar($this->client);
        $result = $calendar_service->events->quickAdd($calendar_id, $new_event_name);
        return json_encode($result);
    }
    public function edit_event(Request $request)
    {
    }
    public function delete_event(Request $request)
    {
    }

    // Search stuff
    public function google_search($query)
    {
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

    // General functions
    public function get_authenticated_url($request_url)
    {
        $token = $this->client->getAccessToken();
        $header = array();
        $header[] = "Content-type: application/atom+xml";
        $header[] = 'Authorization: OAuth '.$token['access_token'];
        $header[] = 'GData-Version: 3.0';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_HTTPGET, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_URL, $request_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $api_response = trim(curl_exec($curl));
        $curl_error = curl_error($curl);
        curl_close($curl);
        return $api_response;
    }

    // Maps stuff
    public static function get_static_map($address)
    {
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
        foreach ($parameters as $key => $value) {
            $static_maps_endpoint .= $key . '=' . $value . '&';
        }
        $hyperlink = 'https://www.google.com/maps/place/'. urlencode($address);
        $output = '<a href="'.$hyperlink.'" target="_blank"><img src="'.$static_maps_endpoint.'" ></a>';
        return $output;
    }

    // Drive stuff
    public function search_drive($query = '')
    {
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

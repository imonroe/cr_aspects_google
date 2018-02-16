<?php

namespace imonroe\cr_aspects_google;

use imonroe\cr_aspects_google\Http\Controllers\GoogleController;
use Illuminate\Support\Facades\Redirect;

class GoogleContactsAPIResultsAspect extends \imonroe\cr_network_aspects\APIResultAspect{
	public function notes_schema(){
		return parent::notes_schema();
	}
	public function create_form($subject_id, $aspect_type_id=null){
		return parent::create_form($subject_id, $this->aspect_type);
	}
	public function edit_form($id){
		return parent::edit_form($id);
	}
	public function display_aspect(){
		$output = '<p>Google Contacts API cached results</p>';
		$decoded = json_decode($this->aspect_data, true);
		//$decoded_str  = var_export($decoded, true);
		//dd($decoded);
		if (!empty($decoded['feed']['entry'] )){
			foreach ($decoded['feed']['entry'] as $contact){
				$output .= '<p><pre>'.var_export($contact, true).'</pre></p>'.PHP_EOL;
			}
		}
		//$output .= parent::display_aspect();
		return $output;
	}
	public function parse(){
		if (empty($this->last_parsed) || strtotime($this->last_parsed) < strtotime('now -1 hour') ){
		//if (true){
			Log::info('Parsing Google Contacts API Results Aspect');
			$google_agent = new \App\Http\Controllers\GoogleController;
			$agent_response = $google_agent->get_contacts();
			if (!is_null($agent_response)){
				$this->aspect_data = $google_agent->get_contacts();
				$this->last_parsed = Carbon::now();
				$this->update_aspect();
				$this->update_people_subjects();
			}
			Log::info('Finished Parsing Google Contacts API Results Aspect');
		}
	}

	public function update_people_subjects(){
		$decoded = json_decode($this->aspect_data, true);
		foreach ($decoded['feed']['entry'] as $contact){
			//$output .= '<p><pre>'.var_export($contact, true).'</pre></p>'.PHP_EOL;
			$subject_name = (!empty($contact['title']['$t'])) ? $contact['title']['$t'] : null;
			Log::info('Trying to check a subject name like: '.$subject_name);
			if ($subject_name){
				$subject_exists = Subject::exists($subject_name);
				if (!$subject_exists){
					// try and create it.
					Log::info('Adding a new Google Contact.');
					$subject_type = SubjectType::where('type_name', '=', 'People')->first();
					$new_subject = new Subject;
					$new_subject->name = $subject_name;
					$new_subject->subject_type = $subject_type->id;
					$new_subject->save();

					// add the google contact results as an aspect
					$aspect_type_id = AspectType::where('aspect_name', '=', 'Google Contact Data')->first()->id;
					$contact_aspect = AspectFactory::make_from_aspect_type($aspect_type_id);
					$contact_aspect->aspect_data = json_encode($contact);
					$contact_aspect->save();
					$new_subject->aspects()->attach($contact_aspect->id);
				} 
				else {
					Log::info($subject_name . ' already exists.');
					// check to see if you should update it.
				}
			}
		}
	}

}  // End of the GoogleContactsAPIResultsAspectclass.

class GoogleContactDataAspect extends \imonroe\crps\Aspect{
	public function notes_schema(){
		return parent::notes_schema();
	}
	public function create_form($subject_id, $aspect_type_id=null){
		return parent::create_form($subject_id, $this->aspect_type);
	}
	public function edit_form($id){
		return parent::edit_form($id);
	}
	public function display_aspect(){
		$contact_array = json_decode($this->aspect_data, true);
		$output = '';
		if (!empty($contact_array['link'])){
			foreach ($contact_array['link'] as $link){
				if ( $link['type'] == 'image/*' ) {
					$profile_image = $link['href'];
				}
				if ( $link['type'] == 'self'  ){
					$contact_link = $link['href'];
				}
				if ( $link['type'] == 'edit'  ){
					$edit_link = $link['href'];
				}
			}
		}
		$full_name = $contact_array['gd$name']['gd$fullName']['$t'];
		$gender = (!empty($contact_array['gContact$gender']['value'])) ? $contact_array['gContact$gender']['value'] : null;
		$birthday = (!empty($contact_array['gContact$birthday']['when'])) ? $contact_array['gContact$birthday']['when'] : null; 
		$organizations = array(); 
		if (!empty($contact_array['gd$organization'])){
			foreach ($contact_array['gd$organization'] as $org){
				$organizations[$org['gd$orgName']['$t']] = (!empty($org['gd$orgTitle']['$t'])) ? $org['gd$orgTitle']['$t'] : null ;
			}
		}
		$email_addresses = array();
		if (!empty($contact_array['gd$email'])){
			foreach ($contact_array['gd$email'] as $email){
			$email_addresses[] = '<a href="mailto:'.$email['address'].'">'.$email['address'].'</a>';
		}
		}
		$phone_numbers = array();
		if (!empty($contact_array['gd$phoneNumber'])){
			foreach ($contact_array['gd$phoneNumber'] as $phone){
			$phone_numbers[] = '<a href="'.$phone['uri'].'">'.$phone['$t'].'</a>';
		}
		}
		$mailing_addresses = array();
		if (!empty($contact_array['gd$structuredPostalAddress'])){
			foreach ($contact_array['gd$structuredPostalAddress'] as $address){
			$add = array();
			$add['formatted'] = (!empty($address['gd$formattedAddress']['$t']))  ? $address['gd$formattedAddress']['$t'] : null;
			$add['street'] = (!empty($address['gd$street']['$t']))  ? $address['gd$street']['$t'] : null;
			$add['city'] = (!empty($address['gd$city']['$t']))  ? $address['gd$city']['$t'] : null;
			$add['state'] = (!empty($address['gd$region']['$t']))  ? $address['gd$region']['$t'] : null;
			$add['zip'] = (!empty($address['gd$postcode']['$t']))  ? $address['gd$postcode']['$t'] : null;
			$add['google_maps_link'] =  \App\Http\Controllers\GoogleController::get_static_map($add['formatted']);
			$mailing_addresses[] = $add;
		}
		}
		$websites = array();
		if (!empty($contact_array['gContact$website'])){
			foreach ($contact_array['gContact$website'] as $website){
			$websites[] = '<a href="'.$website['href'].'" target="_blank">'.$website['href'].'</a>';
		}
		}
		$output .= '<div class="google-contact-info">'.PHP_EOL;

		if (isset($profile_image) && false){
			$google_agent = new \App\Http\Controllers\GoogleController;
			$authenticated_reply = $google_agent->get_authenticated_url($profile_image);
			//dd($authenticated_reply);
			$output .= '<div class="google-contact-profile-photo" style="width:50%; margin:10px; float:left;">'.PHP_EOL;
			$output .= '<img src = "'.$authenticated_reply.'" style="width:100%;">'.PHP_EOL;
			$output .= '</div>'.PHP_EOL;
		}

		$output .= '<p>Full Name: '.$full_name.'</p>'.PHP_EOL;
		$output .= '<p>Gender: '.$gender.'</p>'.PHP_EOL;
		$output .= '<p>DOB: '.$birthday.'</p>'.PHP_EOL;
		$output .= '<p>Organizations: </p>'.PHP_EOL;
		$output .= '<ul>'.PHP_EOL;
		if (!empty($organizations)){
			foreach ($organizations as $key => $value){
				$output .= '<li>'.$key.' - '.$value.'</li>'.PHP_EOL;
			}
		}
		$output .= '</ul>'.PHP_EOL;
		$output .= '<p>Email Addresses: </p>'.PHP_EOL;
		$output .= '<ul>'.PHP_EOL;
		foreach ($email_addresses as $key => $value){
			$output .= '<li>'.$value.'</li>'.PHP_EOL;
		}
		$output .= '</ul>'.PHP_EOL;

		$output .= '<p>Phone Numbers: </p>'.PHP_EOL;
		$output .= '<ul>'.PHP_EOL;
		foreach ($phone_numbers as $key => $value){
			$output .= '<li>'.$value.'</li>'.PHP_EOL;
		}
		$output .= '</ul>'.PHP_EOL;

		$output .= '<p>Mailing Addresses: </p>'.PHP_EOL;
		$output .= '<ul>'.PHP_EOL;
		foreach ($mailing_addresses as $key => $value){
			$output .= '<li>'.$value['formatted'];
			if (!empty($value['google_maps_link'])){
				$output .= $value['google_maps_link'];
			}
			$output .= '</li>'.PHP_EOL;
		}
		$output .= '</ul>'.PHP_EOL;

		$output .= '<p>Web Sites: </p>'.PHP_EOL;
		$output .= '<ul>'.PHP_EOL;
		foreach ($websites as $key => $value){
			$output .= '<li>'.$value.'</li>'.PHP_EOL;
		}
		$output .= '</ul>'.PHP_EOL;

		//$output .= '<a href="'.$edit_link.'" class="btn btn-primary">Edit with Google</a>';

		$output .= '</div>'.PHP_EOL;
		//$output .= '<pre>'.var_export($contact_array, true).'</pre>';
		return $output;
	}

	public function parse(){}
}  // End of the GoogleContactDataAspectclass.

class GoogleTasksListAspect extends \App\LamdaFunctionAspect{
	public function notes_schema(){
		//array('list_id'=>'', 'list_title'=>'Today\'s TODO List', 'css_id'=>'1')
		$settings = json_decode(parent::notes_schema(), true);
		$settings['list_id'] = '@default';
		return json_encode($settings);
	}
	public function create_form($subject_id, $aspect_type_id=null){
		// $lists_agent =  new GoogleController;
		$output = '';
		$output .= '<new-google-tasklist v-bind:subject-id="'.$subject_id.'" v-bind:aspect-type="'.$aspect_type_id.'" ></new-google-tasklist>';
		return $output;
	}
	public function edit_form($id){
		// $lists_agent =  new GoogleController;
		$settings = $this->get_aspect_notes_array();
		$output = '';
		$output .= '<new-google-tasklist v-bind:subject-id="'.$this->subject_id().'" v-bind:aspect-type="'.$this->aspect_type.'" v-bind:aspect-id="'.$this->id.'" settings-list-id="'.$settings['list_id'].'" title="'.$this->title.'" ></new-google-tasklist>';
		return $output;
	}

	public function display_aspect(){
		$settings = $this->get_aspect_notes_array();
		return '<google-tasklist settings-list-id="'.$settings['list_id'].'" ></google-tasklist>';
	}

	public function display_aspect_old(){
		$settings = (!is_null($this->aspect_notes)) ? json_decode($this->aspect_notes, true) : json_decode($this->notes_schema(), true);
		if (empty($settings['list_id']) || $settings['list_id'] == '@default'){
			$function_id = '';
		} else {
			$function_id = $settings['list_id'];
		}
		$spinner = '<center>'. \imonroe\ana\Ana::loading_spinner() . '</center>';
		$csfr_token = csrf_token();
		$output = <<<OUTPUT_STRING
<div class="widget" id="todo_list_{$settings['css_id']}">
		<h3>{$settings['list_title']}</h3>
        <form class="form-inline" id="new_task_form_{$settings['css_id']}">
          <input name="due" type="hidden" value="" />
		  <input name="_token" type="hidden" value="$csfr_token" >
          <input name="task_list" type="hidden" value="{$function_id}" >
          <input name="action" type="hidden" value="new_todo_item" >
          <input name="new_task_title" type="text" class="form-control" id="new_task_title" placeholder="Add a new task">
          <button type="submit" class="btn" id="new_task_submit">Submit</button>
        </form>
    	<div id="todo_stage_{$settings['css_id']}" style="">$spinner</div>
        <script type="text/javascript">
			$(function(){
				$("#new_task_form_{$settings['css_id']}").submit(function(event){
					event.preventDefault();
					var fd = $(this).serialize();
					console.log(fd);
					var url = '/gtasks';
					$.ajax({
							type: 'POST',
							mimeType: 'multipart/form-data',
							url: url,
							data: fd
					})
					.done(function(html){
							$.get( "/gtasks/{$function_id}")
								.done(function( data ) {
									$("#todo_stage_{$settings['css_id']}").html(data);
								});
							$("#new_task_form_{$settings['css_id']}").trigger("reset");
					});
				});// end task submit
				$.get( "/gtasks/{$function_id}")
            			.done(function( data ) {
            			$("#todo_stage_{$settings['css_id']}").html(data);
        		});
			});

			function closeTodoItem_$function_id(item){
				var item_id = item.getAttribute('id');
				$.post( "/gtasks", 
						{ action: "complete_todo_item", task_id: item_id, _token: '$csfr_token', list_id:'{$function_id}'})
            			.done(function( data ) {
            				$.get( "/gtasks/{$function_id}")
							.done(function( data ) {
							$("#todo_stage_{$settings['css_id']}").html(data);
						});
        		});
			}
        </script>
</div>
OUTPUT_STRING;
		//return $output;
	}
	public function parse(){}
	public function lambda_function(){
		return 'lambda_function output';
	}
}  // End of the GoogleTasksListAspectclass.

class GoogleCalendarAspect extends \App\LamdaFunctionAspect{

	public function notes_schema(){
		$settings = json_decode(parent::notes_schema(), true);
		$settings['calendar_title'] = 'Calendar';
		$settings['calendar_id'] = 'primary';
		$settings['css_id'] = 'default_todo';
		return json_encode($settings);
	}
	public function create_form($subject_id, $aspect_type_id=null){
		return parent::create_form($subject_id, $this->aspect_type);
	}
	public function edit_form($id){
		return parent::edit_form($id);
	}
	public function display_aspect(){
		$settings = (!is_null($this->aspect_notes)) ? json_decode($this->aspect_notes, true) : json_decode($this->notes_schema(), true);
		$calendar_title = $settings['calendar_title'];
		$csfr_token = csrf_token();
		$spinner = '<center>'.\imonroe\ana\Ana::loading_spinner().'</center>';
		$output = '';
		$output .= <<<OUTPUT_STRING

<div class="widget" id="google_calendar_{$settings['calendar_id']}">
   
   <div id="calendar_display_{$settings['calendar_id']}" style="float:left; width:255px;margin:.25em;"></div>

   <div style="float:left; clear:none; margin:.5em;">
	<form class="form-inline" id="new_appointment_form" style="margin-left:15px;">
          <div class="form-group">
          <input name="calendar_id" type="hidden" value="{$settings['calendar_id']}" />
          <input name="action" type="hidden" value="new_appointment" />
 		  <input name="_token" type="hidden" value="$csfr_token" />
          <input name="new_appointment_txt" type="text" class="form-control" id="new_appointment_text" placeholder="Add a new appointment">
          <button type="submit" class="btn" id="new_appointment_submit">Submit</button>
          </div>
        </form>
        <div id="calendar_stage">
			$spinner
		</div>
	</div>
		<div style="clear:both;"></div>
        <script type="text/javascript">
			$(function(){
			    // display datepicker
				$("#calendar_display_{$settings['calendar_id']}").datepicker();
				$("#new_appointment_form").submit(function(event){
					event.preventDefault();
					var fd = $(this).serialize();
					var url = '/gcal';
					$.ajax({
							type: 'POST',
							mimeType: 'multipart/form-data',
							url: url,
							data: fd
					})
					.done(function(html){
							$.get( "/gcal")
								.done(function( data ) {
									$("#calendar_stage").html(data);
								});
							$("#new_appointment_form").trigger("reset");
					});
				});// end task submit

				 $.get( "/gcal")
					  .done(function( data ) {
					  $("#calendar_stage").html(data);
				  });
			});
        </script>
</div>		
OUTPUT_STRING;
		return $output;
	}
	public function parse(){}

	public function lambda_function(){
		return 'lambda_function output';
	}
}  // End of the GoogleCalendarAspectclass.
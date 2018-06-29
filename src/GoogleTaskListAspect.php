<?php

namespace imonroe\cr_aspects_google;

use imonroe\cr_aspects_google\Http\Controllers\GoogleController;
use Illuminate\Support\Facades\Redirect;
use imonroe\crps\Aspect;
use imonroe\cr_basic_aspects\LambdaFunctionAspect;
use imonroe\cr_network_aspects\APIResultAspect;

class GoogleTasksListAspect extends LambdaFunctionAspect{
	public function notes_schema(){
		$settings = json_decode(parent::notes_schema(), true);
		$settings['list_id'] = '@default';
		return json_encode($settings);
	}
	public function create_form($subject_id, $aspect_type_id=null){
		// Check to make sure we can create a client, if not we'll take care of it now.
		$output = '';
		$gc = new GoogleController;
		$output .= '<new-google-tasklist v-bind:subject-id="'.$subject_id.'" v-bind:aspect-type="'.$aspect_type_id.'" ></new-google-tasklist>';
		return $output;
	}
	public function edit_form(){
		$output = '';
		$gc = new GoogleController;
		$settings = $this->get_aspect_notes_array();
		$output .= '<new-google-tasklist v-bind:subject-id="'.$this->subject_id().'" v-bind:aspect-type="'.$this->aspect_type.'" v-bind:aspect-id="'.$this->id.'" settings-list-id="'.$settings['list_id'].'" title="'.$this->title.'" ></new-google-tasklist>';
		return $output;
	}

	public function display_aspect(){
		$output = '';
		$gc = new GoogleController;
		$settings = $this->get_aspect_notes_array();
		$output .= '<google-tasklist settings-list-id="'.$settings['list_id'].'" ></google-tasklist>';
		return $output;
	}

	public function parse(){}
	public function lambda_function(){
		return '';
	}
}  // End of the GoogleTasksListAspectclass.
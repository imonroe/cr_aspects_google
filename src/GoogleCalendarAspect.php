<?php

namespace imonroe\cr_aspects_google;

use imonroe\cr_aspects_google\Http\Controllers\GoogleController;
use Illuminate\Support\Facades\Redirect;
use imonroe\crps\Aspect;
use imonroe\cr_basic_aspects\LambdaFunctionAspect;
use imonroe\cr_network_aspects\APIResultAspect;

class GoogleCalendarAspect extends LambdaFunctionAspect
{
    public function notes_schema()
    {
        $settings = json_decode(parent::notes_schema(), true);
        $settings['calendar_id'] = 'primary';
        return json_encode($settings);
    }
    public function create_form($subject_id, $aspect_type_id = null)
    {
        $output = '';
        $gc = new GoogleController;
        $output .= '<new-google-calendar aspect-type="'. $this->aspect_type .'" subject-id="'.$subject_id.'"> </new-google-calendar>';
        return $output;
    }
    public function edit_form()
    {
        $output = '';
        $gc = new GoogleController;
        $output .= parent::edit_form();
        return $output;
    }
    public function display_aspect()
    {
        $output = '';
        $gc = new GoogleController;
        $settings = $this->get_aspect_notes_array();
        $output .= '<google-calendar aspect-data="" v-bind:aspect-id="'.$this->id.'" aspect-notes="" aspect-source="" v-bind:aspect-type="'.$this->aspect_type.'" settings-calendar-id="'.$settings['calendar_id'].'" v-bind:subject-id="'.$this->subject_id().'" ></google-calendar>';
        return $output;
    }
    public function parse()
    {
    }

    public function lambda_function()
    {
        return 'lambda_function output';
    }
}  // End of the GoogleCalendarAspectclass.

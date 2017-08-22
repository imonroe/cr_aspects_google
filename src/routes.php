<?php 


use Illuminate\Http\Request;

Route::namespace('imonroe\cr_aspects_google\Http\Controllers')->group(
    function () {
        Route::middleware(['auth', 'web'])->group(
            function () {
				// Google API routes
				Route::get('gtasks/{task_list_id}', 'GoogleController@display_task_list');
				Route::get('gtasks/', 'GoogleController@display_task_list');
				Route::post('gtasks', 'GoogleController@edit_task_list');
				Route::get('gcal', 'GoogleController@get_calendar');
				Route::post('gcal', 'GoogleController@edit_calendar');
            }
        );
    }
);
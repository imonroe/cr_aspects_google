<?php 

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use imonroe\cr_aspects_google\Http\Controllers\GoogleController;

Route::namespace('imonroe\cr_aspects_google\Http\Controllers')->group(
    function () {
        Route::middleware(['web'])->group(function(){
            // This is the route that handles OAuth2 callbacks from Google.
            Route::get('auth/google/callback', function(){
                $req = request();
                $code = $req->query('code');
                $session = $req->session();
                $user = Auth::user();
                $gc = new GoogleController;
                $client = $gc->get_client();
                if ( !empty($code) && !empty($user) && !empty($user) ){
                    $client->authenticate($code);
                    $google_client_token = $client->getAccessToken();
                    $user->google_token = json_encode($google_client_token);
                    $user->save();
                } else {
                    throw \Exception('Couldn\'t get a token from Google.');
                }
                Redirect::to('home/')->send();
            });

            // Routes for Google Tasks
            Route::post('gtasks/new_list', 'GoogleController@new_task_list');
            Route::get('gtasks/available_lists', 'GoogleController@get_all_task_lists');
            Route::get('gtasks/list/{task_list_id}', 'GoogleController@display_task_list');
            Route::post('gtasks/task/add', 'GoogleController@new_task');
            Route::post('gtasks/task/edit', 'GoogleController@edit_task');
            Route::post('gtasks/task/complete', 'GoogleController@complete_task');
            Route::post('gtasks/task/remove', 'GoogleController@remove_task');

            Route::get('gcal/available_calendars', 'GoogleController@list_calendars');
            Route::get('gcal/calendar', 'GoogleController@get_calendar');
            Route::post('gcal/calendar/create', 'GoogleController@create_calendar');
            Route::post('gcal/calendar/{calendar_id}/edit', 'GoogleController@create_calendar');
            Route::get('gcal/event', 'GoogleController@get_event');
            Route::post('gcal/event/create', 'GoogleController@create_event');
            Route::post('gcal/event/edit', 'GoogleController@edit_eventt');
            Route::post('gcal/event/delete', 'GoogleController@delete_event');
        });

        Route::middleware(['google'])->group(function(){
            
        });
    }
);
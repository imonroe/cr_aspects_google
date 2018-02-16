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
			//Route::get('gtasks/', 'GoogleController@display_task_list');
            //Route::post('gtasks', 'GoogleController@edit_task_list');

            // Routes for Google Calendar
            Route::get('gcal', 'GoogleController@get_calendar');
            Route::post('gcal', 'GoogleController@edit_calendar');
            
            // Routes for Google Contacts


        });
    }
);
<?php 

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use imonroe\cr_aspects_google\Http\Controllers\GoogleController;

Route::namespace('imonroe\cr_aspects_google\Http\Controllers')->group(
    function () {
        Route::middleware(['auth', 'web'])->group(
            function () {
                // Google API routes
                // OAuth2 Callback route first.
                Route::get('auth/google/callback', 'GoogleController@handle_provider_callback');

				Route::get('gtasks/{task_list_id}', 'GoogleController@display_task_list');
				Route::get('gtasks/', 'GoogleController@display_task_list');
                Route::post('gtasks', 'GoogleController@edit_task_list');
                Route::post('gtasks/new_list', 'GoogleController@new_task_list');
				Route::get('gcal', 'GoogleController@get_calendar');
				Route::post('gcal', 'GoogleController@edit_calendar');
            }
        );
        Route::middleware(['web'])->group(function(){
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

        });
    }
);
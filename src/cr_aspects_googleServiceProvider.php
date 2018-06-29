<?php

namespace imonroe\cr_aspects_google;

use Google_Client;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;

class cr_aspects_googleServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        //parent::boot();
        // Migrations:
        $this->loadMigrationsFrom(__DIR__.'/migrations');

        // Views:
        //$this->loadViewsFrom(__DIR__.'/path/to/views', 'courier');
        //$this->publishes([
        //	__DIR__.'/path/to/views' => resource_path('views/vendor/courier'),
        //]);

        // Routes:
        $this->loadRoutesFrom(__DIR__.'/Http/routes.php');

        $this->publishes([
            __DIR__.'/config/google.php' => config_path('google.php'),
        ]);

        $preferences_registry = app()->make('ApplicationPreferencesRegistry');
        $google_pref = [
            'preference' => 'google_aspects_enabled', 
            'preference_label' => 'Enable Google Aspect Types?', 
            'field_type' => 'checkbox', 
            'default_value' => False 
        ];
        $preferences_registry->register_preference($google_pref);
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(){
        $this->app->singleton('CR_Google_Client', function ($app) {
            $client = new Google_Client();
            $client_secret = json_encode([ 'web' => config('google') ]);
            Storage::disk('local')->put('client_secret.json', $client_secret);
            $client->setAuthConfig(Storage::path('client_secret.json'));
            return $client;
        });
    }
}
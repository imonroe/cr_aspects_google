<?php

/**
 * Google API configuration
 */

 return [
    'client_id' => env('GOOGLE_API_CLIENT_ID'),
    'project_id' => env('GOOGLE_PROJECT_ID'),
    'auth_uri' => 'https://accounts.google.com/o/oauth2/auth',
    'token_uri' => 'https://accounts.google.com/o/oauth2/token',
    'auth_provider_x509_cert_url' => 'https://www.googleapis.com/oauth2/v1/certs',
    'client_secret' => env('GOOGLE_API_CLIENT_SECRET'),
    'redirect_uris' => [
        env('APP_URL').'/auth/google/callback',
    ],
    'javascript_origins' => [
        env('APP_URL'),
    ]
 ];

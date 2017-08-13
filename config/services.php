<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Facebook Connection
    |--------------------------------------------------------------------------
    |
    | Connects facebook using the following parameters.
    |
    | For example: https://auth0.com/docs/connections/social/facebook
    |
    */

    'facebook' => [
        'client_id'     =>  env('FACEBOOK_ID'),
        'client_secret' =>  env('FACEBOOK_SECRET'),
        'redirect'      =>  env('FACEBOOK_REDIRECT')
    ],

    /*
    |--------------------------------------------------------------------------
    | Facebook Connection
    |--------------------------------------------------------------------------
    |
    | Connects facebook using the following parameters.
    |
    | For example:
    | https://developers.google.com/identity/sign-in/web/devconsole-project
    |
    */

    'google' => [
        'client_id'     =>  env('GOOGLE_ID'),
        'client_secret' =>  env('GOOGLE_SECRET'),
        'redirect'      =>  env('GOOGLE_REDIRECT')
    ],


];

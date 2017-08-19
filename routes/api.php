<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$api = $app->make(Dingo\Api\Routing\Router::class);

$api->version('v1', function ($api) {
    $api->post('/login', [
        'as' => 'api.login',
        'uses' => 'App\Http\Controllers\Auth\AuthController@postLogin',
    ]);

    $api->get('/login/{provider}', [
        'as' => 'api.login.provider',
        'uses' => 'App\Http\Controllers\Auth\AuthController@getProviderRedirectUrl',
    ]);

    $api->get('/login/{provider}/callback', [
        'as' => 'api.login.provider.callback',
        'uses' => 'App\Http\Controllers\Auth\AuthController@handleCallbackProvider',
    ]);

    $api->post('/signUp', [
        'as' => 'api.signUp',
        'uses' => 'App\Http\Controllers\SignUp\SignUpController@signUp',
    ]);

    $api->group([
        'middleware' => 'api.auth',
    ], function ($api) {
        $api->get('/auth/user', [
            'uses' => 'App\Http\Controllers\Auth\AuthController@getUser',
            'as' => 'api.auth.user'
        ]);
        $api->patch('/auth/refresh', [
            'uses' => 'App\Http\Controllers\Auth\AuthController@patchRefresh',
            'as' => 'api.auth.refresh'
        ]);
        $api->delete('/auth/invalidate', [
            'uses' => 'App\Http\Controllers\Auth\AuthController@deleteInvalidate',
            'as' => 'api.auth.invalidate'
        ]);
    });
});

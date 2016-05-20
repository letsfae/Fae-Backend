<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
use Illuminate\Http\Response;
Route::get('/', function () {
    return view('Fae API v1');
});

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function ($api) {
    $API_ROOT = 'App\Api\v1\Controllers\\';
    $api->post('users', $API_ROOT.'UserController@signUp');
    $api->post('authentication', $API_ROOT.'AuthenticationController@login');
    $api->delete('authentication', $API_ROOT.'AuthenticationController@logout');
});
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

Route::get('/', function () {
	return view('welcome', ['title' => 'Fae API v1']);
});

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function ($api) {
    $api->post('users', 'App\Api\v1\Controllers\UserController@signUp');
    $api->post('authentication', 'App\Api\v1\Controllers\AuthenticationController@login');

    // verification
    $api->post('reset_login/code', 'App\Api\v1\Controllers\ResetLoginController@sendResetCode');
    $api->put('reset_login/code', 'App\Api\v1\Controllers\ResetLoginController@verifyResetCode');
    $api->post('reset_login/password', 'App\Api\v1\Controllers\ResetLoginController@resetPassword');

    // existence
    $api->get('existence/email/{email}', 'App\Api\v1\Controllers\ExistenceController@email');
    $api->get('existence/user_name/{user_name}', 'App\Api\v1\Controllers\ExistenceController@userName');
});

$api->version('v1', ['middleware' => 'api.auth', 'providers' => ['fae']], function ($api) {
    $api->delete('authentication', 'App\Api\v1\Controllers\AuthenticationController@logout');

    // profile
    $api->post('users/profile', 'App\Api\v1\Controllers\UserController@updateSelfProfile');
    $api->get('users/profile', 'App\Api\v1\Controllers\UserController@getSelfProfile');
    $api->get('users/{user_id}/profile', 'App\Api\v1\Controllers\UserController@getProfile');
    // avatar
    $api->post('files/avatar', 'App\Api\v1\Controllers\FileEntryController@setSelfAvatar');
    $api->get('files/avatar', 'App\Api\v1\Controllers\FileEntryController@getSelfAvatar');
    $api->get('files/avatar/{user_id}', 'App\Api\v1\Controllers\FileEntryController@getAvatar');
    //synchronization
    $api->get('/sync', 'App\Api\v1\Controllers\SyncController@getSync');
    //map
    $api->get('/map', 'App\Api\v1\Controllers\MapController@getMap');
    $api->post('/map/user', 'App\Api\v1\Controllers\MapController@updateUserLocation');
    $api->post('/map/active', 'App\Api\v1\Controllers\MapController@setActive');
    $api->get('/map/active', 'App\Api\v1\Controllers\MapController@getActive');
    //comment
    $api->post('/comments', 'App\Api\v1\Controllers\CommentController@createComment');
    $api->get('/comments/{comment_id}', 'App\Api\v1\Controllers\CommentController@getComment');
    $api->delete('/comments/{comment_id}', 'App\Api\v1\Controllers\CommentController@deleteComment');
    $api->get('/comments/users/{user_id}', 'App\Api\v1\Controllers\CommentController@getUserComments');
});
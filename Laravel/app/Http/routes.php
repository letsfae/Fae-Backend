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
    $api->post('/users', 'App\Api\v1\Controllers\UserController@signUp');
    $api->post('/authentication', 'App\Api\v1\Controllers\AuthenticationController@login');

    // verification
    $api->post('/reset_login/code', 'App\Api\v1\Controllers\ResetLoginController@sendResetCode');
    $api->post('/reset_login/code/verify', 'App\Api\v1\Controllers\ResetLoginController@verifyResetCode');
    $api->post('/reset_login/password', 'App\Api\v1\Controllers\ResetLoginController@resetPassword');

    // existence
    $api->get('/existence/email/{email}', 'App\Api\v1\Controllers\ExistenceController@email');
    $api->get('/existence/user_name/{user_name}', 'App\Api\v1\Controllers\ExistenceController@userName');

    // avatar (* for front-end requirement)
    $api->get('/files/avatar/{user_id}', 'App\Api\v1\Controllers\FileEntryController@getAvatar');
});

$api->version('v1', ['middleware' => 'api.auth', 'providers' => ['fae']], function ($api) {
    $api->delete('/authentication', 'App\Api\v1\Controllers\AuthenticationController@logout');

    // account
    $api->post('/users/account', 'App\Api\v1\Controllers\UserController@updateAccount');
    $api->get('/users/account', 'App\Api\v1\Controllers\UserController@getAccount');
    $api->post('/users/account/password', 'App\Api\v1\Controllers\UserController@updatePassword');
    $api->post('/users/account/password/verify', 'App\Api\v1\Controllers\UserController@verifyPassword');
    $api->post('/users/account/email', 'App\Api\v1\Controllers\UserController@updateEmail');
    $api->post('/users/account/email/verify', 'App\Api\v1\Controllers\UserController@verifyEmail');
    $api->post('/users/account/phone', 'App\Api\v1\Controllers\UserController@updatePhone');
    $api->post('/users/account/phone/verify', 'App\Api\v1\Controllers\UserController@verifyPhone');
    // profile
    $api->post('/users/profile', 'App\Api\v1\Controllers\UserController@updateSelfProfile');
    $api->get('/users/profile', 'App\Api\v1\Controllers\UserController@getSelfProfile');
    $api->get('/users/{user_id}/profile', 'App\Api\v1\Controllers\UserController@getProfile');
    // $api->post('/users/profile/privacy', 'App\Api\v1\Controllers\UserController@updatePrivacy');
    // $api->get('/users/profile/privacy', 'App\Api\v1\Controllers\UserController@getPrivacy');
    // status
    $api->post('/users/status', 'App\Api\v1\Controllers\UserController@updateSelfStatus');
    $api->get('/users/status', 'App\Api\v1\Controllers\UserController@getSelfStatus');
    $api->get('/users/{user_id}/status', 'App\Api\v1\Controllers\UserController@getStatus');
    // avatar
    $api->post('/files/avatar', 'App\Api\v1\Controllers\FileEntryController@setSelfAvatar');
    $api->get('/files/avatar', 'App\Api\v1\Controllers\FileEntryController@getSelfAvatar');
    // $api->get('/files/avatar/{user_id}', 'App\Api\v1\Controllers\FileEntryController@getAvatar'); // moved to unauth
    // synchronization
    $api->get('/sync', 'App\Api\v1\Controllers\SyncController@getSync');
    // map
    $api->get('/map', 'App\Api\v1\Controllers\MapController@getMap');
    $api->post('/map/user', 'App\Api\v1\Controllers\MapController@updateUserLocation');
    // comment
    $api->post('/comments', 'App\Api\v1\Controllers\CommentController@createComment');
    $api->get('/comments/{comment_id}', 'App\Api\v1\Controllers\CommentController@getComment');
    $api->delete('/comments/{comment_id}', 'App\Api\v1\Controllers\CommentController@deleteComment');
    $api->get('/comments/users/{user_id}', 'App\Api\v1\Controllers\CommentController@getUserComments');
    // friends
    $api->post('/friends/request', 'App\Api\v1\Controllers\FriendController@requestFriend');
    $api->get('/friends/request', 'App\Api\v1\Controllers\FriendController@getAllRequests');
    $api->post('/friends/accept', 'App\Api\v1\Controllers\FriendController@acceptRequest');
    $api->post('/friends/ignore', 'App\Api\v1\Controllers\FriendController@ignoreRequest');
    $api->delete('/friends/{user_id}', 'App\Api\v1\Controllers\FriendController@deleteFriend');
    // chats    

});
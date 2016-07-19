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
    $api->post('/users/profile', 'App\Api\v1\Controllers\UserProfileController@updateSelfProfile');
    $api->get('/users/profile', 'App\Api\v1\Controllers\UserProfileController@getSelfProfile');
    $api->get('/users/{user_id}/profile', 'App\Api\v1\Controllers\UserProfileController@getProfile');
    // $api->post('/users/profile/privacy', 'App\Api\v1\Controllers\UserProfileController@updatePrivacy');
    // $api->get('/users/profile/privacy', 'App\Api\v1\Controllers\UserProfileController@getPrivacy');
    // name card
    $api->get('/users/{user_id}/name_card', 'App\Api\v1\Controllers\UserNameCardController@getNameCard');
    $api->get('/users/name_card', 'App\Api\v1\Controllers\UserNameCardController@getSelfNameCard');
    $api->get('/users/name_card/tags', 'App\Api\v1\Controllers\UserNameCardController@getAllTags');
    $api->post('/users/name_card', 'App\Api\v1\Controllers\UserNameCardController@updateNameCard');
    // status
    $api->post('/users/status', 'App\Api\v1\Controllers\UserController@updateSelfStatus');
    $api->get('/users/status', 'App\Api\v1\Controllers\UserController@getSelfStatus');
    $api->get('/users/{user_id}/status', 'App\Api\v1\Controllers\UserController@getStatus');
    // synchronization
    $api->get('/sync', 'App\Api\v1\Controllers\SyncController@getSync');
    // map
    $api->get('/map', 'App\Api\v1\Controllers\MapController@getMap');
    $api->post('/map/user', 'App\Api\v1\Controllers\MapController@updateUserLocation');
    // comment
    $api->post('/comments', 'App\Api\v1\Controllers\CommentController@create');
    $api->post('/comments/{comment_id}', 'App\Api\v1\Controllers\CommentController@update');
    $api->get('/comments/{comment_id}', 'App\Api\v1\Controllers\CommentController@getOne');
    $api->delete('/comments/{comment_id}', 'App\Api\v1\Controllers\CommentController@delete');
    $api->get('/comments/users/{user_id}', 'App\Api\v1\Controllers\CommentController@getFromUser');
    // media
    $api->post('/medias', 'App\Api\v1\Controllers\MediaController@create');
    $api->post('/medias/{media_id}', 'App\Api\v1\Controllers\MediaController@update');
    $api->get('/medias/{media_id}', 'App\Api\v1\Controllers\MediaController@getOne');
    $api->delete('/medias/{media_id}', 'App\Api\v1\Controllers\MediaController@delete');
    $api->get('/medias/users/{user_id}', 'App\Api\v1\Controllers\MediaController@getFromUser');
    );
    // faevor
    $api->post('/faevors', 'App\Api\v1\Controllers\FaevorController@create');
    $api->post('/faevors/{faevor_id}', 'App\Api\v1\Controllers\FaevorController@update');
    $api->get('/faevors/{faevor_id}', 'App\Api\v1\Controllers\FaevorController@getOne');
    $api->delete('/faevors/{faevor_id}', 'App\Api\v1\Controllers\FaevorController@delete');
    $api->get('/faevors/users/{user_id}', 'App\Api\v1\Controllers\FaevorController@getFromUser');
    // friends
    $api->post('/friends/request', 'App\Api\v1\Controllers\FriendController@requestFriend');
    $api->get('/friends/request', 'App\Api\v1\Controllers\FriendController@getAllRequests');
    $api->post('/friends/accept', 'App\Api\v1\Controllers\FriendController@acceptRequest');
    $api->post('/friends/ignore', 'App\Api\v1\Controllers\FriendController@ignoreRequest');
    $api->delete('/friends/{user_id}', 'App\Api\v1\Controllers\FriendController@deleteFriend');
    // chats
    $api->post('/chats', 'App\Api\v1\Controllers\ChatController@send');
    $api->get('/chats/unread', 'App\Api\v1\Controllers\ChatController@getUnread');
    $api->post('/chats/read', 'App\Api\v1\Controllers\ChatController@markRead');
    $api->get('/chats', 'App\Api\v1\Controllers\ChatController@getHistory');
    $api->delete('/chats/{chat_id}', 'App\Api\v1\Controllers\ChatController@delete');
    // tags
    $api->post('/tags', 'App\Api\v1\Controllers\TagController@create');
    $api->get('/tags', 'App\Api\v1\Controllers\TagController@getArray');
    $api->get('/tags/{tag_id}', 'App\Api\v1\Controllers\TagController@getOne');
});

/**
 * Files
 */
$api->version('v1', function ($api) {
    // * no auth due to the request of front-end
    $api->get('/files/{file_id}/data', 'App\Api\v1\Controllers\FileController@getData');
    $api->get('/files/users/{user_id}/avatar', 'App\Api\v1\Controllers\UserFileController@getAvatar');
    $api->get('/files/users/{user_id}/name_card_photo/{position}', 'App\Api\v1\Controllers\UserFileController@getNameCardPhoto');
});

$api->version('v1', ['middleware' => 'api.auth', 'providers' => ['fae']], function ($api) {
    // general
    $api->post('/files', 'App\Api\v1\Controllers\FileController@upload');
    $api->get('/files/{file_id}/attribute', 'App\Api\v1\Controllers\FileController@getAttribute');
    // avatar
    $api->post('/files/users/avatar', 'App\Api\v1\Controllers\UserFileController@setSelfAvatar');
    $api->get('/files/users/avatar', 'App\Api\v1\Controllers\UserFileController@getSelfAvatar');
    // name card photo
    $api->post('/files/users/name_card_photo', 'App\Api\v1\Controllers\UserFileController@updateNameCardPhoto');
    $api->delete('/files/users/name_card_photo/{position}', 'App\Api\v1\Controllers\UserFileController@deleteNameCardPhoto');
    $api->get('/files/users/name_card_photo/{position}', 'App\Api\v1\Controllers\UserFileController@getSelfNameCardPhoto');
});
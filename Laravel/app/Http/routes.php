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

$api->version('v1', ['namespace' => 'App\Api\v1\Controllers'], function ($api) {
    $api->post('/users', 'UserController@signUp');
    $api->post('/authentication', 'AuthenticationController@login');
    $api->get('/guest_authentication', 'AuthenticationController@guestLogin');

    // verification
    $api->post('/reset_login/code', 'ResetLoginController@sendResetCode');
    $api->post('/reset_login/code/verify', 'ResetLoginController@verifyResetCode');
    $api->post('/reset_login/password', 'ResetLoginController@resetPassword');

    // existence
    $api->get('/existence/email/{email}', 'ExistenceController@email');
    $api->get('/existence/user_name/{user_name}', 'ExistenceController@userName');
    $api->get('/existence/phone/_batch', 'ExistenceController@getUserByPhoneBatched');
    
    // test
    $api->post('/test/push_notification', 'TestController@sendPushNotification');
});

$api->version('v1', ['middleware' => 'api.auth', 'providers' => ['guest'], 'namespace' => 'App\Api\v1\Controllers'], function ($api) {
    $api->get('/map', 'MapController@getMap');
    // search
    $api->get('/search', 'SearchController@search');
    $api->post('/search', 'SearchController@search');
    $api->get('/search/bulk', 'SearchController@bulk');
    $api->post('/search/bulk', 'SearchController@bulk');

    $api->get('/users/{user_id}/name_card', 'UserNameCardController@getNameCard');

    $api->get('/files/users/{user_id}/avatar', 'UserFileController@getAvatarMaxSize');
    $api->get('/files/users/{user_id}/avatar/{size}', 'UserFileController@getAvatar');
    
    $api->get('/files/users/{user_id}/name_card_cover', 'UserFileController@getNameCardCover');
});

$api->version('v1', ['middleware' => 'api.auth', 'providers' => ['fae'], 'namespace' => 'App\Api\v1\Controllers'], function ($api) {
    $api->delete('/authentication', 'AuthenticationController@logout');

    // account
    $api->post('/users/account', 'UserController@updateAccount');
    $api->get('/users/account', 'UserController@getAccount');
    $api->post('/users/account/password', 'UserController@updatePassword');
    $api->post('/users/account/password/verify', 'UserController@verifyPassword');
    $api->post('/users/account/email', 'UserController@updateEmail');
    $api->post('/users/account/email/verify', 'UserController@verifyEmail');
    $api->post('/users/account/phone', 'UserController@updatePhone');
    $api->post('/users/account/phone/verify', 'UserController@verifyPhone');
    $api->delete('/users/account/phone', 'UserController@deletePhone');
    // profile
    $api->post('/users/profile', 'UserProfileController@updateSelfProfile');
    $api->get('/users/profile', 'UserProfileController@getSelfProfile');
    $api->get('/users/{user_id}/profile', 'UserProfileController@getProfile');
    $api->post('/users/profile/privacy', 'UserProfileController@updatePrivacy');
    $api->get('/users/profile/privacy', 'UserProfileController@getPrivacy');
    // name card
    
    $api->get('/users/name_card', 'UserNameCardController@getSelfNameCard');
    $api->get('/users/name_card/tags', 'UserNameCardController@getAllTags');
    $api->post('/users/name_card', 'UserNameCardController@updateNameCard');
    $api->post('/users/{user_id}/name_card/save', 'UserNameCardController@saveNameCard');
    $api->delete('/users/{user_id}/name_card/save', 'UserNameCardController@unsaveNameCard');
    $api->get('/users/name_card/saved', 'UserNameCardController@getSavedNameCardList');
    // settings
    $api->get('/users/settings', 'UserSettingController@get');
    $api->post('/users/settings', 'UserSettingController@update');
    // status
    $api->post('/users/status', 'UserController@updateSelfStatus');
    $api->get('/users/status', 'UserController@getSelfStatus');
    $api->get('/users/{user_id}/status', 'UserController@getStatus');
    // relation
    $api->get('/users/relation/{user_id}', 'UserController@getRelation');
    // synchronization
    $api->get('/sync', 'SyncController@getSync');
    // map
    //$api->get('/map', 'MapController@getMap');
    $api->post('/map/user', 'MapController@updateUserLocation');
    // // comment
    // $api->post('/comments', 'CommentController@create');
    // $api->post('/comments/{comment_id}', 'CommentController@update');
    // $api->get('/comments/{comment_id}', 'CommentController@getOne');
    // $api->delete('/comments/{comment_id}', 'CommentController@delete');
    // $api->get('/comments/users/{user_id}', 'CommentController@getFromUser');
    // // media
    // $api->post('/medias', 'MediaController@create');
    // $api->post('/medias/{media_id}', 'MediaController@update');
    // $api->get('/medias/{media_id}', 'MediaController@getOne');
    // $api->delete('/medias/{media_id}', 'MediaController@delete');
    // $api->get('/medias/users/{user_id}', 'MediaController@getFromUser');
    // // faevor
    // $api->post('/faevors', 'FaevorController@create');
    // $api->post('/faevors/{faevor_id}', 'FaevorController@update');
    // $api->get('/faevors/{faevor_id}', 'FaevorController@getOne');
    // $api->delete('/faevors/{faevor_id}', 'FaevorController@delete');
    // $api->get('/faevors/users/{user_id}', 'FaevorController@getFromUser');
    // friends
    $api->post('/friends/request', 'FriendController@requestFriend');
    $api->get('/friends/request', 'FriendController@getAllRequests');
    $api->get('/friends/request_sent', 'FriendController@getAllSentRequests');
    $api->post('/friends/accept', 'FriendController@acceptRequest');
    $api->post('/friends/ignore', 'FriendController@ignoreRequest');
    $api->post('/friends/withdraw', 'FriendController@withdrawRequest');
    $api->delete('/friends/{user_id}', 'FriendController@deleteFriend');
    $api->get('/friends', 'FriendController@getFriendsList');
    // follows
    $api->post('/follows', 'FollowController@follow');
    $api->get('/follows/{user_id}/followee', 'FollowController@getFollowee');
    $api->get('/follows/{user_id}/follower', 'FollowController@getFollower');
    $api->delete('/follows/{followee_id}', 'FollowController@unfollow');
    // blocks
    $api->post('/blocks', 'BlockController@add');
    $api->get('/blocks', 'BlockController@get');
    $api->delete('/blocks/{user_id}', 'BlockController@delete');
    // chats
    $api->post('/chats', 'ChatController@send');
    $api->get('/chats/unread', 'ChatController@getUnread');
    $api->post('/chats/read', 'ChatController@markRead');
    $api->get('/chats', 'ChatController@getHistory');
    $api->delete('/chats/{chat_id}', 'ChatController@delete');
    $api->get('/chats/users/{user_a_id}/{user_b_id}', 'ChatController@getChatIdFromUserId');
    $api->get('/chats/{user_a_id}/{user_b_id}', 'ChatController@getMessageByUserId');
    $api->get('/chats/{chat_id}', 'ChatController@getMessageByChatId');
    // chats v2
    $api->post('/chats_v2', 'ChatV2Controller@send');
    $api->get('/chats_v2/unread', 'ChatV2Controller@getUnread');
    $api->delete('/chats_v2/{chat_id}', 'ChatV2Controller@delete');
    $api->get('/chats_v2/users/{user_a_id}/{user_b_id}', 'ChatV2Controller@getChatIdFromUserId');
    $api->get('/chats_v2/{user_a_id}/{user_b_id}', 'ChatV2Controller@getMessageByUserId');
    $api->get('/chats_v2/{chat_id}', 'ChatV2Controller@getMessageByChatId');
    // chat rooms
    $api->post('/chat_rooms', 'ChatRoomController@create');
    $api->post('/chat_rooms/{chat_room_id}', 'ChatRoomController@update');
    $api->get('/chat_rooms/{chat_room_id}', 'ChatRoomController@getOne');
    // $api->delete('/chat_rooms/{chat_room_id}', 'ChatRoomController@delete');
    $api->get('/chat_rooms/users/{user_id}', 'ChatRoomController@getFromUser');
    $api->post('/chat_rooms/{chat_room_id}/message', 'ChatRoomController@send');
    $api->get('/chat_rooms/message/unread', 'ChatRoomController@getUnread');
    $api->post('/chat_rooms/{chat_room_id}/message/read', 'ChatRoomController@markRead');
    $api->get('/chat_rooms', 'ChatRoomController@getHistory');
    $api->get('/chat_rooms/{chat_room_id}/users', 'ChatRoomController@getUserList');
    // chat rooms v2
    $api->post('/chat_rooms_v2', 'ChatRoomV2Controller@create');
    $api->post('/chat_rooms_v2/{chat_room_id}', 'ChatRoomV2Controller@update');
    $api->get('/chat_rooms_v2/{chat_room_id}', 'ChatRoomV2Controller@getOne');
    $api->get('/chat_rooms_v2/users/{user_id}', 'ChatRoomV2Controller@getFromUser');
    $api->post('/chat_rooms_v2/{chat_room_id}/message', 'ChatRoomV2Controller@send');
    $api->get('/chat_rooms_v2/message/unread', 'ChatRoomV2Controller@getUnread');
    $api->post('/chat_rooms_v2/{chat_room_id}/message/read', 'ChatRoomV2Controller@markRead');
    $api->get('/chat_rooms_v2/{chat_room_id}', 'ChatRoomV2Controller@getMessage');
    $api->get('/chat_rooms_v2/{chat_room_id}/users', 'ChatRoomV2Controller@getUserList');
    // tags
    $api->post('/tags', 'TagController@create');
    $api->get('/tags', 'TagController@getArray');
    $api->get('/tags/{tag_id}', 'TagController@getOne');
    $api->get('/tags/{tag_id}/pin', 'TagController@getAllPins');
    // pin operations
    $api->post('/pins/{type}/{pin_id}/read', 'PinOperationController@read');
    $api->post('/pins/{type}/{pin_id}/like', 'PinOperationController@like');
    $api->delete('/pins/{type}/{pin_id}/like', 'PinOperationController@unlike');
    $api->post('/pins/{type}/{pin_id}/comments', 'PinOperationController@comment');
    $api->post('/pins/comments/{pin_comment_id}', 'PinOperationController@updateComment');
    $api->delete('/pins/comments/{pin_comment_id}', 'PinOperationController@uncomment');
    // $api->post('/pins/{type}/{pin_id}/save', 'PinOperationController@save'); // deprecated
    // $api->delete('/pins/{type}/{pin_id}/save', 'PinOperationController@unsave'); // deprecated
    $api->post('/pins/{type}/{pin_id}/memo', 'PinOperationController@memo');
    $api->delete('/pins/{type}/{pin_id}/memo', 'PinOperationController@unmemo');
    $api->post('/pins/{type}/{pin_id}/feeling', 'PinOperationController@feeling');
    $api->delete('/pins/{type}/{pin_id}/feeling', 'PinOperationController@removeFeeling');
    $api->get('/pins/{type}/{pin_id}/attribute', 'PinOperationController@getPinAttribute');
    $api->get('/pins/{type}/{pin_id}/comments', 'PinOperationController@getPinCommentList');
    $api->get('/pins/saved', 'PinOperationController@getSavedPinList');
    $api->get('/pins/users', 'PinOperationController@getSelfPinList');
    $api->get('/pins/users/{user_id}', 'PinOperationController@getUserPinList');
    $api->post('/pins/comments/{pin_comment_id}/vote', 'PinOperationController@vote');
    $api->delete('/pins/comments/{pin_comment_id}/vote', 'PinOperationController@cancelVote');
    $api->get('/pins/statistics', 'PinOperationController@pinStatistics');
    // richtext (hashtags and at)
    $api->get('/richtext/hashtag/{hashtag}', 'RichTextController@searchHashtag');
    $api->get('/richtext/hashtag/{hashtag}/contains', 'RichTextController@searchHashtagWithText');
    // feedback
    $api->post('/feedback', 'FeedbackController@sendFeedback');
    // places
    $api->get('/places/{place_id}', 'PlaceController@getOne');
    // locations
    $api->post('/locations', 'LocationController@create');
    $api->post('/locations/{location_id}', 'LocationController@update');
    $api->get('/locations/{location_id}', 'LocationController@getOne');
    $api->delete('/locations/{location_id}', 'LocationController@delete');
    $api->get('/locations', 'LocationController@getFromCurrentUser');
    // collections
    $api->post('/collections', 'CollectionController@createOne');
    $api->get('/collections', 'CollectionController@getAll');
    $api->post('/collections/{collection_id}', 'CollectionController@updateOne');
    $api->delete('/collections/{collection_id}', 'CollectionController@deleteOne');
    $api->get('/collections/{collection_id}', 'CollectionController@getOne');
    $api->post('/collections/{collection_id}/save/{type}/{pin_id}', 'CollectionController@save');
    $api->delete('/collections/{collection_id}/save/{type}/{pin_id}', 'CollectionController@unsave');
    
});

/**
 * Files
 */
$api->version('v1', ['middleware' => 'api.auth', 'providers' => ['fae'], 'namespace' => 'App\Api\v1\Controllers'], function ($api) {
    // general
    $api->post('/files', 'FileController@upload');
    $api->get('/files/{file_id}/attribute', 'FileController@getAttribute');
    $api->get('/files/{file_id}/data', 'FileController@getData');
    // avatar
    $api->post('/files/users/avatar', 'UserFileController@setSelfAvatar');
    $api->get('/files/users/avatar', 'UserFileController@getSelfAvatarMaxSize');
    $api->get('/files/users/avatar/{size}', 'UserFileController@getSelfAvatar');
    // name card cover
    $api->post('/files/users/name_card_cover', 'UserFileController@setSelfNameCardCover');
    $api->get('/files/users/name_card_cover', 'UserFileController@getSelfNameCardCover');
    // name card photo
    $api->post('/files/users/name_card_photo', 'UserFileController@updateNameCardPhoto');
    $api->delete('/files/users/name_card_photo/{position}', 'UserFileController@deleteNameCardPhoto');
    $api->get('/files/users/name_card_photo/{position}', 'UserFileController@getSelfNameCardPhoto');
    $api->get('/files/users/{user_id}/name_card_photo/{position}', 'UserFileController@getNameCardPhoto');
    // char room cover
    $api->post('/files/chat_rooms/cover_image', 'ChatRoomFileController@setChatRoomCoverImage');
    $api->get('/files/chat_rooms/{chat_room_id}/cover_image', 'ChatRoomFileController@getChatRoomCoverImage');
    $api->get('/files/places/{place_id}', 'PlaceController@getImage');
});
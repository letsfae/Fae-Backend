<?php
namespace App\Api\v1\Utilities;

use App\Api\v1\Controllers\MediaController;
use App\Api\v1\Controllers\CommentController;
use App\Api\v1\Controllers\ChatRoomController;

class PinUtility
{
    public static function getPinObject($type, $pin_id, $user_id) {
        if($type == 'media') {
        	return MediaController::getPinObject($pin_id, $user_id);
        } else if($type == 'comment') {
        	return CommentController::getPinObject($pin_id, $user_id);
        } else if($type == 'chat_room') {
        	return ChatRoomController::getPinObject($pin_id, $user_id);
        }
        return null;
    }

}
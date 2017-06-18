<?php
namespace App\Api\v1\Utilities;

use App\Api\v1\Controllers\MediaController;
use App\Api\v1\Controllers\CommentController;
use App\Api\v1\Controllers\ChatRoomController;
use App\Api\v1\Controllers\PlaceController;

class PinUtility
{
    public static function getPinObject($type, $pin_id, $user_id) {
        if ($type == 'media') {
        	return MediaController::getPinObject($pin_id, $user_id);
        } else if ($type == 'comment') {
        	return CommentController::getPinObject($pin_id, $user_id);
        } else if ($type == 'chat_room') {
        	return ChatRoomController::getPinObject($pin_id, $user_id);
        } else if ($type == 'place') {
            return PlaceController::getPinObject($pin_id, $user_id);
        } else if ($type == 'location') {
            return LocationController::getPinObject($pin_id, $user_id);
        }
        return null;
    }

    public static function encodeFeelings($arr) {
        return implode(',', $arr);
    }

    public static function decodeFeelings($str) {
        //return explode(',', $str);
        return array_map('intval', explode(',', $str));
    }

    public static function increaseFeelingCount($str, $num) {
        $arr = PinUtility::decodeFeelings($str);
        $arr[$num]++;
        return PinUtility::encodeFeelings($arr);
    }

    public static function decreaseFeelingCount($str, $num) {
        $arr = PinUtility::decodeFeelings($str);
        $arr[$num]--;
        return PinUtility::encodeFeelings($arr);
    }

}
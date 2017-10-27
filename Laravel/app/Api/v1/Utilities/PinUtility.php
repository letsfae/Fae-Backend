<?php
namespace App\Api\v1\Utilities;

use App\Api\v1\Controllers\MediaController;
use App\Api\v1\Controllers\CommentController;
use App\Api\v1\Controllers\ChatRoomController;
use App\Api\v1\Controllers\PlaceController;
use App\Api\v1\Controllers\LocationController;
use App\Users;
use App\Places;
use App\Locations;
use App\ChatRooms;
use App\Collections;
use App\Collection_of_pins;

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

    public static function encode($arr) {
        if(count($arr) == 0) return null;
        return implode(',', $arr);
    }

    public static function decode($str) {
        //return explode(',', $str);
        if($str == null) return array();
        return array_map('intval', explode(',', $str));
    }

    public static function increaseFeelingCount($str, $num) {
        $arr = PinUtility::decode($str);
        $arr[$num]++;
        return PinUtility::encode($arr);
    }

    public static function decreaseFeelingCount($str, $num) {
        $arr = PinUtility::decode($str);
        $arr[$num]--;
        return PinUtility::encode($arr);
    }

    public static function AddIdToList($str, $id) {
        $ids = PinUtility::decode($str);
        foreach ($ids as $saved_id) {
            if ($saved_id == $id) {
                return $str;
            }
        }
        $ids[] = $id;
        return PinUtility::encode($ids);
    }

    public static function deleteIdFromList($str, $id) {
        $ids = PinUtility::decode($str);
        $new_ids = array();
        foreach ($ids as $saved_id) {
            if ($saved_id != $id) {
                $new_ids[] = $saved_id;
            }
        }
        return PinUtility::encode($new_ids);
    }

    // public static function validation($user_id, $id, $type, $pin_id) {
    //     $result = array();
    //     if(!is_numeric($id) || !is_numeric($pin_id)) {
    //         $result[] = true;
    //         $result[] = response()->json([
    //                         'message' => 'collection_id or pin_id is not integer',
    //                         'error_code' => ErrorCodeUtility::INPUT_ID_NOT_NUMERIC,
    //                         'status_code' => '400'
    //                     ], 400);
    //     }
    //     if($type != 'location' && $type != 'place' && $type != 'comment' && $type != 'media')
    //     {
    //         $result[] = true;
    //         $result[] = response()->json([
    //                         'message' => 'wrong type, must be location, media, comment, or place',
    //                         'error_code' => ErrorCodeUtility::WRONG_TYPE,
    //                         'status_code' => '400'
    //                     ], 400);
    //     }

    // }

    public static function validation($type, $id) {
        $result = array();
        if(!is_numeric($id)) {
            $result[] = true;
            $result[] = response()->json([
                            'message' => 'id is not integer',
                            'error_code' => ErrorCodeUtility::INPUT_ID_NOT_NUMERIC,
                            'status_code' => '400'
                        ], 400);
            return $result;
        }
        $obj = null;
        if($type == 'collection') {
            $result[] = true;
            $obj = Collections::where('id',$id)->lockForUpdate()->first();
            if(is_null($obj)) {
                $result[] = response()->json([
                                'message' => 'collection not found',
                                'error_code' => ErrorCodeUtility::COLLECTION_NOT_FOUND,
                                'status_code' => '404'
                            ], 404);
                return $result;
            }
        }
        $result[0] = false;
        $result[] = $obj;
        return $result;
    }
}
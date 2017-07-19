<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Dingo\Api\Routing\Helpers;
use Validator;
use App\ChatRoomUsers;
use App\ChatRooms;
use App\Api\v1\Utilities\ErrorCodeUtility;

class ChatRoomFileController extends Controller
{
    use Helpers;

    public function __construct(Request $request) {
    	$this->request = $request;
    }

    public function setChatRoomCoverImage() {
    	// validation
        $input = $this->request->all();  
        if(!$this->request->hasFile('cover_image') || !$this->request->file('cover_image')->isValid()) {
            return $this->response->errorBadRequest();
        }

        if(!is_numeric($this->request->chat_room_id)){
            return response()->json([
                    'message' => 'chat_room_id is not integer',
                    'error_code' => ErrorCodeUtility::INPUT_ID_NOT_NUMERIC,
                    'status_code' => '400'
                ], 400);
        }

        $chat_room = ChatRooms::find($this->request->chat_room_id);
        if(is_null($chat_room)){
            return response()->json([
                    'message' => 'chat room not found',
                    'error_code' => ErrorCodeUtility::CHAT_ROOM_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        $chat_room_user = ChatRoomUsers::where('chat_room_id', $this->request->chat_room_id)->where('user_id', $this->request->self_user_id)->first();
        if(is_null($chat_room_user)){
        	return response()->json([
                    'message' => 'chat room user not found',
                    'error_code' => ErrorCodeUtility::CHAT_ROOM_USER_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }


        // store file
        $chat_room_id = $this->request->chat_room_id;
        $file = $this->request->cover_image;

        Storage::disk('local')->put('chat_room_cover/'.$chat_room_id.'.jpg', File::get($file));

        return $this->response->created();
    }

    public function getChatRoomCoverImage($chat_room_id) {
    	if(!is_numeric($chat_room_id))        {
            return response()->json([
                    'message' => 'chat_room_id is not integer',
                    'error_code' => ErrorCodeUtility::INPUT_ID_NOT_NUMERIC,
                    'status_code' => '400'
                ], 400);
        } 
    	try {
            $file = Storage::disk('local')->get('chat_room_cover/'.$chat_room_id.'.jpg');
        } catch(\Exception $e) {
            return $this->response->errorNotFound();
        }
        return response($file, 200)->header('Content-Type', 'image/jpeg');
    }
}

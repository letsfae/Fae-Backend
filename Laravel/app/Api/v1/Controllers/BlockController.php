<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Dingo\Api\Routing\Helpers;
use Illuminate\Routing\Controller;
use Auth;
use Validator;

use Dingo\Api\Exception\StoreResourceFailedException;
use Davibennun\LaravelPushNotification\Facades\PushNotification;

use App\Users;
use App\Friends;
use App\Friend_requests;
use App\Sessions;
use App\Blocks;
use App\Api\v1\Controllers\FriendController;
use App\Api\v1\Utilities\ErrorCodeUtility;

class BlockController extends Controller {
    use Helpers;
    
    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function add() {
    	//validation
        $validator = Validator::make($this->request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);
        if($validator->fails()){
            throw new StoreResourceFailedException('Could not request block.',$validator->errors());
        }

        if($this->request->self_user_id == $this->request->user_id) {
            return response()->json([
                'message' => 'Bad request, you can not block yourself!',
                'error_code' => ErrorCodeUtility::BLOCK_SELF,
                'status_code' => '400'
            ], 400);
        }

        //find both users
        $self_user_id = $this->request->self_user_id;
        $self_user = Users::where('id', $self_user_id)->first();
        $requested_user_id = $this->request->user_id;
        $requested_user = Users::where('id', $requested_user_id)->first();
        
        //find the request if exist
        $curr_block = Blocks::where('user_id', $self_user_id)->where('block_id', $requested_user_id)->first();
        if ($curr_block == null) {
            //create friend request and store it
            $new_block = new Blocks;
            $new_block->user_id = $self_user_id;
            $new_block->block_id = $requested_user_id;
            $new_block->save();
            Friend_requests::where('user_id', $self_user_id)->where('requested_user_id', $requested_user_id)->delete();
            Friend_requests::where('user_id', $requested_user_id)->where('requested_user_id', $self_user_id)->delete();
        }
        return $this->response->created();
    }

    public function delete($user_id) {
    	//find both users
        $self_user_id = $this->request->self_user_id;
        $self_user = Users::where('id', $self_user_id)->first();
        if ($self_user == null) {
            return response()->json([
                    'message' => 'user not found',
                    'error_code' => ErrorCodeUtility::USER_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        
        $blocked_user = Users::where('id', $user_id)->first();
        if ($blocked_user == null) {
            return response()->json([
                    'message' => 'user not found',
                    'error_code' => ErrorCodeUtility::USER_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }

    	$curr_block = Blocks::where('user_id', $self_user_id)->where('block_id', $user_id)->first();
        if ($curr_block != null) {
            $curr_block->delete();
        }
        return $this->response->noContent();
    }

    public function get() {
        $blocks = Blocks::where('user_id', $this->request->self_user_id)->get();
        $result = array();
        foreach ($blocks as $block) {
            $user = Users::find($block->block_id);
            $result[] = array('user_id' => $block->block_id, 'user_name' => is_null($user) ? null : $user->user_name);
        }
        return $this->response->array($result);
    }
}

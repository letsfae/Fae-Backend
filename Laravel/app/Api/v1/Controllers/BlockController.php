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
use App\Friendships;
use App\Friend_requests;
use App\Sessions;
use App\Blocks;
use App\Api\v1\Controllers\FriendController;

class BlockController extends Controller {
    use Helpers;
    
    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function add() {
    	//validation
        $validator = Validator::make($this->request->all(), [
            'user_id' => 'required|numeric',
        ]);
        if($validator->fails()){
            throw new StoreResourceFailedException('Could not request block.',$validator->errors());
        }
        
        //find both users
        $self_user_id = $this->request->self_user_id;
        $self_user = Users::where('id', $self_user_id)->first();
        if ($self_user == null) {
            throw new StoreResourceFailedException('Bad request, No such users exist!');
        }
        
        $requested_user_id = $this->request->user_id;
        $requested_user = Users::where('id', $requested_user_id)->first();
        if ($requested_user == null) {
            throw new StoreResourceFailedException('Bad request, Request user incorrect!');
        }
        
        //find the request if exist
        $curr_block = Blocks::where('user_id', $self_user_id)->where('block_id', $requested_user_id)->first();
        if ($curr_block == null) {
            //create friend request and store it
            $new_block = new Blocks;
            $new_block->user_id = $self_user_id;
            $new_block->block_id = $requested_user_id;
            $new_block->save();

            //delete the friendship if exists
	        $curr_friendship = Friendships::where('user_id', $self_user_id)->where('friend_id', $requested_user_id)->first();
	        if ($curr_friendship != null) {
	            $curr_friendship->delete();
	        }
	        
	        $curr_friendship = Friendships::where('friend_id', $self_user_id)->where('user_id', $requested_user_id)->first();
	        if ($curr_friendship != null) {
	            $curr_friendship->delete();
	        }
        }
    }

    public function delete($user_id) {
    	//find both users
        $self_user_id = $this->request->self_user_id;
        $self_user = Users::where('id', $self_user_id)->first();
        if ($self_user == null) {
            throw new StoreResourceFailedException('Bad request, No such users exist!');
        }
        
        $blocked_user = Users::where('id', $user_id)->first();
        if ($blocked_user == null) {
            throw new StoreResourceFailedException('Bad request, Blocked user incorrect!');
        }

    	$curr_block = Blocks::where('user_id', $self_user_id)->where('block_id', $user_id)->first();
        if ($curr_block != null) {
            $curr_block->delete();
        }
    }
}

<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;
use Auth;
use Validator;

use Dingo\Api\Exception\StoreResourceFailedException;
use Davibennun\LaravelPushNotification\Facades\PushNotification;

use Config;

use App\Users;
use App\Friends;
use App\Friend_requests;
use App\Sessions;
use App\Blocks;

class FriendController extends Controller {
    use Helpers;
    
    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function requestFriend() {
        //validation
        $validator = Validator::make($this->request->all(), [
            'requested_user_id' => 'required|numeric',
        ]);
        if($validator->fails()){
            throw new StoreResourceFailedException('Could not request friend.',$validator->errors());
        }
        
        //find both users
        $self_user_id = $this->request->self_user_id;
        $self_user = Users::where('id', $self_user_id)->first();
        if ($self_user == null) {
            throw new StoreResourceFailedException('Bad request, No such users exist!');
        }
        
        $requested_user_id = $this->request->requested_user_id;
        $requested_user = Users::where('id', $requested_user_id)->first();
        if ($requested_user == null) {
            throw new StoreResourceFailedException('Bad request, Request user incorrect!');
        }
        
        //if blocked, then this friendship cannot be set
        $curr_block = Blocks::where('user_id', $self_user_id)->where('block_id', $requested_user_id)->first();
        if ($curr_block != null) {
            throw new StoreResourceFailedException('Bad request, you have already blocked the user!');
        }   

        $curr_block = Blocks::where('user_id', $requested_user_id)->where('block_id', $self_user_id)->first();
        if ($curr_block != null) {
            throw new StoreResourceFailedException('Bad request, you have already been blocked the user!');
        }
        
        //find the request if exist
        $curr_request = Friend_requests::where('user_id', $self_user_id)->where('requested_user_id', $requested_user_id)->first();
        if ($curr_request == null) {
            //create friend request and store it
            $new_friend_requests = new Friend_requests;
            $new_friend_requests->user_id = $self_user_id;
            $new_friend_requests->requested_user_id = $requested_user_id;
            $new_friend_requests->save();
        }
        
        //push back notification to requested user
        if(Config::get('app.pushback')==true) {
            $sessions = Sessions::where('user_id', $requested_user_id)->get();
            foreach($sessions as $key=>$value) {
                if ($value->is_mobile && !empty($value->device_id)){
                    $message = PushNotification::Message('Other user requesting friend',array(
                        'custom' => array('custom data' => array(
                            'requested_user_id' => $requested_user_id
                        ))
                    ));

                    $collection = PushNotification::app('appNameIOS')
                        ->to($value->device_id)
                        ->send($message);

                    // get response for each device push
                    foreach ($collection->pushManager as $push) {
                        $response = $push->getAdapter()->getResponse();
                    }
                }
            }
        }
        
        return $this->response->created();
    }

    public function acceptRequest() {
        //validation
        $validator = Validator::make($this->request->all(), [
            'friend_request_id' => 'required|numeric',
        ]);
        if($validator->fails()){
            throw new StoreResourceFailedException('Could not accept friend request.',$validator->errors());
        }
        
        //find the request by id
        $curr_request_id = $this->request->friend_request_id;
        $curr_request = Friend_requests::where('id', $curr_request_id)->first();
        if ($curr_request == null) {
            throw new StoreResourceFailedException('Bad request, No such friend request!');
        }        

        //if blocked, then this friendship cannot be set
        $self_user_id = $this->request->self_user_id;
        $curr_block = Blocks::where('user_id', $self_user_id)->where('block_id', $curr_request->user_id)->first();
        if ($curr_block != null) {
            throw new StoreResourceFailedException('Bad request, you have already blocked the user!');
        }   

        $curr_block = Blocks::where('user_id', $curr_request->user_id)->where('block_id', $self_user_id)->first();
        if ($curr_block != null) {
            throw new StoreResourceFailedException('Bad request, you have already been blocked the user!');
        }   
        
        //create friendship and store it
        $new_friends = new Friends;
        $new_friends->user_id = $curr_request->user_id;
        $new_friends->friend_id = $curr_request->requested_user_id;
        $new_friends->save();
        
        $new_friends_reverse = new Friends;
        $new_friends_reverse->user_id = $curr_request->requested_user_id;
        $new_friends_reverse->friend_id = $curr_request->user_id;
        $new_friends_reverse->save();
        
        //------- friendship already exist-------
        
        //push back notification to requeste user
        if(Config::get('app.pushback')==true) {
            $sessions = Sessions::where('user_id', $curr_request->user_id)->get();
            foreach($sessions as $key=>$value) {
                if ($value->is_mobile && !empty($value->device_id)){
                    $message = PushNotification::Message('Friend request acceptted',array(
                        'custom' => array('custom data' => array(
                            'requested_user_id' => $curr_request->requested_user_id
                        ))
                    ));

                    $collection = PushNotification::app('appNameIOS')
                        ->to($value->device_id)
                        ->send($message);

                    // get response for each device push
                    foreach ($collection->pushManager as $push) {
                        $response = $push->getAdapter()->getResponse();
                    }
                }
            }
        }
        
        //delete this request
        $curr_request->delete();
        
        return $this->response->created();
    }

    public function ignoreRequest() {
        //validation
        $validator = Validator::make($this->request->all(), [
            'friend_request_id' => 'required|numeric',
        ]);
        if($validator->fails()){
            throw new StoreResourceFailedException('Could not accept friend request.',$validator->errors());
        }
        
        //find the request by id
        $curr_request_id = $this->request->friend_request_id;
        $curr_request = Friend_requests::where('id', $curr_request_id)->first();
        if ($curr_request == null) {
            throw new StoreResourceFailedException('Bad request, No such friend request!');
        }
        
        //------- friendship already exist-------
        
        //push back notification to requeste user
        if(Config::get('app.pushback')==true) {
            $sessions = Sessions::where('user_id', $curr_request->user_id)->get();
            foreach($sessions as $key=>$value) {
                if ($value->is_mobile && !empty($value->device_id)){
                    $message = PushNotification::Message('Friend request acceptted',array(
                        'custom' => array('custom data' => array(
                            'requested_user_id' => $curr_request->requested_user_id
                        ))
                    ));

                    $collection = PushNotification::app('appNameIOS')
                        ->to($value->device_id)
                        ->send($message);

                    // get response for each device push
                    foreach ($collection->pushManager as $push) {
                        $response = $push->getAdapter()->getResponse();
                    }
                }
            }
        }
        
        //delete this request
        $curr_request->delete();
        
        return $this->response->created();
    }

    public function deleteFriend($user_id) {
        //find the friendship by user_id
        $self_user_id = $this->request->self_user_id;
        $curr_friendship = Friends::where('user_id', $self_user_id)->where('friend_id', $user_id)->first();
        if ($curr_friendship != null) {
            //delete this request
            $curr_friendship->delete();
        }
        
        $curr_friendship = Friends::where('friend_id', $self_user_id)->where('user_id', $user_id)->first();
        if ($curr_friendship != null) {
            //delete this request
            $curr_friendship->delete();
        }
        
        return $this->response->noContent();
    }

    public function getAllRequests() {
        $self_user_id = $this->request->self_user_id;
        $friend_requests = Friend_requests::where('requested_user_id', $self_user_id)->get();
        
        $result = array();
        foreach($friend_requests as $key=>$value){
            //find user
            $request_user = Users::where('id', $value->user_id)->first();
            /*if ($request_user == null) {
                throw new StoreResourceFailedException('Bad request, No such users exist!');
            }*/
            $content = array('friend_request_id' => $value->id, 
                             'request_user_id' => $value->user_id, 
                             'request_user_name' => $request_user->user_name,
                             'request_email' => $request_user->email,
                             'created_at' => $value->created_at->format('Y-m-d H:i:s'));
            array_push($result, $content);
        }
        
        return $this->response->array($result);
    }
}

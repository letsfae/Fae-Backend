<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;
use Auth;
use Validator;

use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Exception\UpdateResourceFailedException;
use Davibennun\LaravelPushNotification\Facades\PushNotification;

use Config;

use App\Users;
use App\Friends;
use App\Friend_requests;
use App\Sessions;
use App\Blocks;
use App\User_exts;
use App\Name_cards;
use App\Api\v1\utilities\TimeUtility;
use App\Api\v1\Utilities\ErrorCodeUtility;

class FriendController extends Controller {
    use Helpers;
    
    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function requestFriend() {
        //validation
        $validator = Validator::make($this->request->all(), [
            'requested_user_id' => 'required|numeric',
            'resend' => 'filled|in:true,false'
        ]);
        if($validator->fails()){
            throw new StoreResourceFailedException('Could not request friend.',$validator->errors());
        }
        
        $requested_user_id = $this->request->requested_user_id;
        $self_user_id = $this->request->self_user_id;
        $requested_user = Users::where('id', $requested_user_id)->first();
        if ($requested_user == null) {
            return response()->json([
                    'message' => 'request user not found',
                    'error_code' => ErrorCodeUtility::USER_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }

        if($self_user_id == $requested_user_id) {
            return response()->json([
                'message' => 'Bad request, you can not send friend request to yourself',
                'error_code' => ErrorCodeUtility::FRIEND_REQUEST_SELF,
                'status_code' => '400'
            ], 400);
        }
        
        //if blocked, then this friendship cannot be set
        $curr_block = Blocks::where('user_id', $self_user_id)->where('block_id', $requested_user_id)->first();
        if ($curr_block != null) {
            return response()->json([
                'message' => 'Bad request, you have already blocked this user!',
                'error_code' => ErrorCodeUtility::BLOCKED_ALREADY,
                'status_code' => '400'
            ], 400);
        }   

        $curr_block = Blocks::where('user_id', $requested_user_id)->where('block_id', $self_user_id)->first();
        if ($curr_block != null) {
            return response()->json([
                'message' => 'Bad request, you have already been blocked by the user!',
                'error_code' => ErrorCodeUtility::BLOCKED_ALREADY,
                'status_code' => '400'
            ], 400);
        }

        if(Friends::where('user_id', $this->request->self_user_id)->where('friend_id', $requested_user_id)->exists()) {
            return response()->json([
                'message' => 'Bad request, can not send friend request to your friend',
                'error_code' => ErrorCodeUtility::FRIEND_ALREADY,
                'status_code' => '400'
            ], 400);
        }

        if(Friend_requests::where('user_id', $requested_user_id)->where('requested_user_id', $self_user_id)->exists()) {
            return response()->json([
                'message' => 'Bad request, this user has already sent you a friend request',
                'error_code' => ErrorCodeUtility::RESPONSE_BEFORE_SEND_A_REQUEST,
                'status_code' => '400'
            ], 400);
        }
        
        //find the request if exist
        $curr_request = Friend_requests::where('user_id', $self_user_id)->where('requested_user_id', $requested_user_id)->first();
        if ($curr_request == null) {
            //create friend request and store it
            $new_friend_requests = new Friend_requests;
            $new_friend_requests->user_id = $self_user_id;
            $new_friend_requests->requested_user_id = $requested_user_id;
            $new_friend_requests->save();
        } else if(!$this->request->has('resend') || ($this->request->has('resend') && $this->request->resend == 'false')) {
            return response()->json([
                'message' => 'Bad request, you have already sent a request',
                'error_code' => ErrorCodeUtility::REQUESTED_ALREADY,
                'status_code' => '400'
            ], 400);
        }
        
        //push back notification to requested user
        if(Config::get('app.pushback')==true) {
            $sessions = Sessions::where('user_id', $requested_user_id)->get();
            foreach($sessions as $key=>$value) {
                if ($value->is_mobile && !empty($value->device_id)){
                    $message = PushNotification::Message('Other user requesting friend',array(
                        'custom' => array('custom data' => array(
                            'requested_user_id' => $this->request->self_user_id
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
            'friend_request_id' => 'filled|required_without:requested_user_id|numeric',
            'requested_user_id' => 'filled|required_without:friend_request_id|numeric'
        ]);
        if($validator->fails()){
            throw new StoreResourceFailedException('Could not accept friend request.',$validator->errors());
        }
        
        //find the request by id
        $curr_request = null;
        if($this->request->has('friend_request_id')) {
            $curr_request = Friend_requests::where('id', $this->request->friend_request_id)->first();
        } else {
            $curr_request = Friend_requests::where('user_id', $this->request->requested_user_id)
                                           ->where('requested_user_id', $this->request->self_user_id)
                                           ->first();
        }
        if ($curr_request == null) {
            return response()->json([
                    'message' => 'friend request not found',
                    'error_code' => ErrorCodeUtility::FRIEND_REQUEST_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }        

        //if blocked, then this friendship cannot be set
        $self_user_id = $this->request->self_user_id;
        $curr_block = Blocks::where('user_id', $self_user_id)->where('block_id', $curr_request->user_id)->first();
        if ($curr_block != null) {
            return response()->json([
                'message' => 'Bad request, you have already blocked the user!',
                'error_code' => ErrorCodeUtility::BLOCKED_ALREADY,
                'status_code' => '400'
            ], 400);
        }   

        $curr_block = Blocks::where('user_id', $curr_request->user_id)->where('block_id', $self_user_id)->first();
        if ($curr_block != null) {
            return response()->json([
                'message' => 'Bad request, you have already blocked the user!',
                'error_code' => ErrorCodeUtility::BLOCKED_ALREADY,
                'status_code' => '400'
            ], 400);
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
            'friend_request_id' => 'filled|required_without:requested_user_id|numeric',
            'requested_user_id' => 'filled|required_without:friend_request_id|numeric'
        ]);
        if($validator->fails()){
            throw new StoreResourceFailedException('Could not accept friend request.',$validator->errors());
        }
        
        //find the request by id
        $curr_request = null;
        if($this->request->has('friend_request_id')) {
            $curr_request = Friend_requests::where('id', $this->request->friend_request_id)->first();
        } else {
            $curr_request = Friend_requests::where('user_id', $this->request->requested_user_id)
                                           ->where('requested_user_id', $this->request->self_user_id)
                                           ->first();
        }

        if ($curr_request == null) {
            return response()->json([
                    'message' => 'friend request not found',
                    'error_code' => ErrorCodeUtility::FRIEND_REQUEST_NOT_FOUND,
                    'status_code' => '404'
                ], 404);

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
            if(is_null($request_user))
            {
                return response()->json([
                    'message' => 'user not found',
                    'error_code' => ErrorCodeUtility::USER_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
            }
            $nameCard = Name_cards::find($value->user_id);
            $user_exts = User_exts::find($value->user_id);
            /*if ($request_user == null) {
                throw new StoreResourceFailedException('Bad request, No such users exist!');
            }*/
            $birthDate = $request_user->birthday;
            $birthDate = explode("-", $birthDate);
            $age = (date("md", date("U", mktime(0, 0, 0, $birthDate[1], $birthDate[2], $birthDate[0]))) > date("md")
                    ? ((date("Y") - $birthDate[0]) - 1)
                    : (date("Y") - $birthDate[0]));
            $content = array('friend_request_id' => $value->id, 
                             'request_user_id' => $value->user_id, 
                             'request_user_name' => $user_exts->show_user_name ? 
                                                    $request_user->user_name : null,
                             'request_user_nick_name' => $nameCard->nick_name,
                             'request_user_age' => $nameCard->show_age ? $age : null,
                             'request_user_gender' => $nameCard->show_gender ? $request_user->gender : null,
                             'request_email' => $request_user->email,
                             'created_at' => $value->created_at->format('Y-m-d H:i:s'));
            array_push($result, $content);
        }
        
        return $this->response->array($result);
    }

    public function getAllSentRequests() {
        $friend_requests = Friend_requests::where('user_id', $this->request->self_user_id)->get();
        $result = array();
        foreach ($friend_requests as $friend_request) {
            $requested_user = Users::where('id', $friend_request->requested_user_id)->first();
            if(is_null($requested_user))
            {
                return response()->json([
                    'message' => 'user not found',
                    'error_code' => ErrorCodeUtility::USER_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
            }
            $nameCard = Name_cards::find($friend_request->requested_user_id);
            $user_exts = User_exts::find($friend_request->requested_user_id);
            $birthDate = $requested_user->birthday;
            $birthDate = explode("-", $birthDate);
            $age = (date("md", date("U", mktime(0, 0, 0, $birthDate[1], $birthDate[2], $birthDate[0]))) > date("md")
                    ? ((date("Y") - $birthDate[0]) - 1)
                    : (date("Y") - $birthDate[0]));
            $content = array('friend_request_id' => $friend_request->id, 
                             'requested_user_id' => $friend_request->requested_user_id, 
                             'requested_user_name' => $user_exts->show_user_name ? $requested_user->user_name : null,
                             'requested_user_nick_name' => $nameCard->nick_name,
                             'requested_user_age' => $nameCard->show_age ? $age : null,
                             'requested_user_gender' => $nameCard->show_gender ? $requested_user->gender : null,
                             'requested_email' => $requested_user->email,
                             'created_at' => $friend_request->created_at->format('Y-m-d H:i:s'));
            array_push($result, $content);
        }
        return $this->response->array($result);
    }

    public function getFriendsList() {
        $friends = Friends::where('user_id', $this->request->self_user_id)->get();
        $info = array();
        foreach ($friends as $friend) 
        {
            $user = Users::find($friend->friend_id);
            if(is_null($user))
            {
                return response()->json([
                    'message' => 'user not found',
                    'error_code' => ErrorCodeUtility::USER_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
            }
            $nameCard = Name_cards::find($friend->friend_id);
            $user_exts = User_exts::find($friend->friend_id);
            $birthDate = $user->birthday;
            $birthDate = explode("-", $birthDate);
            $age = (date("md", date("U", mktime(0, 0, 0, $birthDate[1], $birthDate[2], $birthDate[0]))) > date("md")
                    ? ((date("Y") - $birthDate[0]) - 1)
                    : (date("Y") - $birthDate[0]));
            $info[] = array('friend_id' => $friend->friend_id,
                            'friend_user_name' => $user_exts->show_user_name ? $user->user_name : null,
                            'friend_user_nick_name' => $nameCard->nick_name,
                            'friend_user_age' => $nameCard->show_age ? $age : null,
                            'friend_user_gender' => $nameCard->show_gender ? $user->gender : null);
        }
        return $this->response->array($info);
    }

    public function withdrawRequest() {
        $validator = Validator::make($this->request->all(), [
            'friend_request_id' => 'filled|required_without:requested_user_id|numeric',
            'requested_user_id' => 'filled|required_without:friend_request_id|numeric'
        ]);
        if($validator->fails()){
            throw new UpdateResourceFailedException('Could not accept friend request.',$validator->errors());
        }

        $curr_request = null;
        if($this->request->has('friend_request_id')) {
            $curr_request = Friend_requests::where('id', $this->request->friend_request_id)->first();
        } else {
            $curr_request = Friend_requests::where('user_id', $this->request->self_user_id)
                                           ->where('requested_user_id', $this->request->requested_user_id)
                                           ->first();
        }
        if ($curr_request == null) {
            return response()->json([
                    'message' => 'friend request not found',
                    'error_code' => ErrorCodeUtility::FRIEND_REQUEST_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        if($curr_request->user_id != $this->request->self_user_id) {
            return response()->json([
                    'message' => 'Can not withdraw the request because you are not the creator of this request, ',
                    'error_code' => ErrorCodeUtility::NOT_REQUESTER_OF_REQUEST,
                    'status_code' => '403'
                ], 403);
        }
        $curr_request->delete();
        return $this->response->noContent();
    }
}

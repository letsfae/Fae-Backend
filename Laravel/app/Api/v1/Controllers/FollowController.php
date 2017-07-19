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
use App\Follows;
use App\User_exts;
use App\Name_cards;
use App\Api\v1\Utilities\ErrorCodeUtility;

class FollowController extends Controller
{
    use Helpers;
    
    public function __construct(Request $request) 
    {
        $this->request = $request;
    }

    public function follow() 
    {
        $this->followValidation($this->request);
        if(!Users::where('id', $this->request->followee_id)->exists()) 
        {
            return response()->json([
                'message' => 'user not found',
                'error_code' => ErrorCodeUtility::USER_NOT_FOUND,
                'status_code' => '404'
            ], 404);
        }
        if($this->request->self_user_id == $this->request->followee_id) {
            return response()->json([
                'message' => 'Bad request, you can not follow yourself!',
                'error_code' => ErrorCodeUtility::FOLLOW_SELF,
                'status_code' => '400'
            ], 400);
        }
        $follow = Follows::where('user_id', $this->request->self_user_id)
                         ->where('followee_id', $this->request->followee_id)->first();
        if(!is_null($follow))
        {
            return response()->json([
                'message' => 'Bad request, you have already followed this user!',
                'error_code' => ErrorCodeUtility::FOLLOWED_ALREADY,
                'status_code' => '400'
            ], 400);
        }
        $follow = new Follows();
        $follow->user_id = $this->request->self_user_id;
        $follow->followee_id = $this->request->followee_id;
        $follow->save();

        if(Config::get('app.pushback')==true) {
            $sessions = Sessions::where('user_id', $this->request->followee_id)->get();
            foreach($sessions as $key=>$value) {
                if ($value->is_mobile && !empty($value->device_id)){
                    $message = PushNotification::Message('you have been followed by someone',array(
                        'custom' => array('custom data' => array(
                            'follower_id' => $this->request->followee_id
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
    
    public function getFollowee($user_id) 
    {
        if(!is_numeric($user_id))
        {
            return response()->json([
                    'message' => 'user_id is not integer',
                    'error_code' => ErrorCodeUtility::INPUT_ID_NOT_NUMERIC,
                    'status_code' => '400'
                ], 400);
        }
        $follows = Follows::where('user_id', $user_id)->get();
        $info = array();
        foreach ($follows as $follow) 
        {
            $user = Users::find($follow->followee_id);
            if(is_null($user))
            {
                return response()->json([
                    'message' => 'user not found',
                    'error_code' => ErrorCodeUtility::USER_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
            }
            $nameCard = Name_cards::find($follow->followee_id);
            $user_exts = User_exts::find($follow->followee_id);
            $birthDate = $user->birthday;
            $birthDate = explode("-", $birthDate);
            $age = (date("md", date("U", mktime(0, 0, 0, $birthDate[1], $birthDate[2], $birthDate[0]))) > date("md")
                    ? ((date("Y") - $birthDate[0]) - 1)
                    : (date("Y") - $birthDate[0]));
            $info[] = array('followee_id' => $follow->followee_id, 
                            'followee_user_name' => $user_exts->show_user_name ? $user->user_name : null,
                            'followee_user_nick_name' => $nameCard->nick_name,
                            'followee_user_age' => $nameCard->show_age ? $age : null,
                            'followee_user_gender' => $nameCard->show_gender ? $user->gender : null);
        }
        return $this->response->array($info);
    } 

    public function getFollower($user_id) 
    {
        if(!is_numeric($user_id))
        {
            return response()->json([
                    'message' => 'user_id is not integer',
                    'error_code' => ErrorCodeUtility::INPUT_ID_NOT_NUMERIC,
                    'status_code' => '400'
                ], 400);
        }
        $follows = Follows::where('followee_id', $user_id)->get();
        $info = array();
        foreach ($follows as $follow) 
        { 
            $user = Users::find($follow->user_id);
            if(is_null($user))
            {
                return response()->json([
                    'message' => 'user not found',
                    'error_code' => ErrorCodeUtility::USER_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
            }
            $nameCard = Name_cards::find($follow->user_id);
            $user_exts = User_exts::find($follow->user_id);
            $birthDate = $user->birthday;
            $birthDate = explode("-", $birthDate);
            $age = (date("md", date("U", mktime(0, 0, 0, $birthDate[1], $birthDate[2], $birthDate[0]))) > date("md")
                    ? ((date("Y") - $birthDate[0]) - 1)
                    : (date("Y") - $birthDate[0]));
            $info[] = array('follower_id' => $follow->user_id, 
                            'follower_user_name' => $user_exts->show_user_name ? $user->user_name : null,
                            'follower_user_nick_name' => $nameCard->nick_name,
                            'follower_user_age' => $nameCard->show_age ? $age : null,
                            'follower_user_gender' => $nameCard->show_gender ? $user->gender : null);
        }
        return $this->response->array($info);
    }

    public function unfollow($followee_id) 
    {
        if(!is_numeric($followee_id))
        {
            return response()->json([
                    'message' => 'followee_id is not integer',
                    'error_code' => ErrorCodeUtility::INPUT_ID_NOT_NUMERIC,
                    'status_code' => '400'
                ], 400);
        }
        $follow = Follows::where('user_id', $this->request->self_user_id)->where('followee_id', $followee_id)->first();
        if(is_null($follow))
        {
            return response()->json([
                'message' => 'Bad request, you have not followed this user yet!',
                'error_code' => ErrorCodeUtility::NOT_FOLLOWED,
                'status_code' => '400'
            ], 400);
        }
        $follow->delete();
        return $this->response->noContent();
    }

    private function followValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'followee_id' => 'required|numeric|min:0'
        ]);
        if($validator->fails())
        {
            throw new StoreResourceFailedException('Could not follow this person.',$validator->errors());
        }
    }
}

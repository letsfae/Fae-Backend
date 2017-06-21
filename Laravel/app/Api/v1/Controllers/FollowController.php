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
use App\Api\v1\Utilities\ErrorCodeUtility;

class FriendController extends Controller {
    use Helpers;
    
    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function follow() {

    }   
    
    public function getFollowee($user_id) {

    } 

    public function getFollower($user_id) {

    }

    public function unfollow($followee_id) {
        
    }
}

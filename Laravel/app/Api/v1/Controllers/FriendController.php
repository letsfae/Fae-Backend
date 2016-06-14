<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;
use Auth;
use Validator;

class FriendController extends Controller {
    use Helpers;
    
    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function requestFriend() {

    }

    public function acceptFriend() {

    }

    public function ignoreFriend() {

    }

    public function deleteFriend($user_id) {
        
    }

    public function getAllRequests() {

    }
}

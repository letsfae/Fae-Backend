<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;
use App\Friend_requests;
use App\Chats;
use App\Sessions;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class SyncController extends Controller {
    use Helpers;
    
    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function getSync() {
    	$to_user_id = $this->request->self_user_id;
    	$friend_requests = Friend_requests::where('requested_user_id', '=', $to_user_id)->get();
    	if ($friend_requests == null) {
			throw new AccessDeniedHttpException('Bad request, No such friend_requests exist!');      
        }
    	else {
    		$friend_request = $friend_requests->count();
    	}
    	$chat = Chats::where('to_user', '=', $to_user_id)->get();
    	if ($friend_requests == null) {
			throw new AccessDeniedHttpException('Bad request, No such chats exist!');      
        }
        else {
    		$sum = 0;
    		foreach ($chat as $chats) {
    			$count = $chats->count();
    			$sum = $count + $sum;
    		}
    	}
    	$session = Sessions::where('user_id', '=', '2')->first();
    	$active = $session->active;
    	$user_active = False;
    	if ($session == null) {
			throw new AccessDeniedHttpException('Bad request, No such sessions exist!');      
        }
        else {
	    	if ($active == 1) {
	    		$user_active = (bool)$active;
	    	}
    	}
    	$info = array('friend_request' => $friend_request, 'chat' => $sum, 'active' => $user_active);
        return $this->response->array($info);
    }
}

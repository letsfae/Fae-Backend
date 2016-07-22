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
        $friend_requests = Friend_requests::where('requested_user_id', $to_user_id)->get();
        if ($friend_requests == null) {
            $friend_request = 0;
        }
        else {
            $friend_request = $friend_requests->count();
        }
        $sum = 0;
        $chats = Chats::where('user_a_id', $this->request->self_user_id)->get();
        if(!is_null($chats))
        {
            $sum += $chats->sum('user_a_unread_count'); 
        }
        $chats = Chats::where('user_b_id', $this->request->self_user_id)->get();
        if(!is_null($chats))
        {
            $sum += $chats->sum('user_b_unread_count'); 
        }
        $info = array('friend_request' => $friend_request, 'chat' => $sum);
        return $this->response->array($info);
    }
}

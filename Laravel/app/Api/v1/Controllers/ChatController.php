<?php
namespace App\Api\v1\Controllers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;

class ChatController extends Controller {
    use Helpers;
    
    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function send() {

    }

    public function getUnread() {
        
    }

    public function markRead() {
        
    }

    public function getHistory() {
        
    }

    public function delete($chat_id) {
        
    }
}


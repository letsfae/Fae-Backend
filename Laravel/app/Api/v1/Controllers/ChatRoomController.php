<?php

namespace App\Api\v1\Controllers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;

class ChatRoomController extends Controller implements PinInterface {
    use Helpers;

    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function create() {
    }

    public function update($chat_room_id) {
    }

    public function getOne($chat_room_id) {
    }

    public function delete($chat_room_id) {
    }

    public function getFromUser($user_id) {
    }

    public function send($chat_room_id) {
    }

    public function getUnread($chat_room_id) {
    }

    public function markRead($chat_room_id) {
    }

    public function getHistory() {
    }
}
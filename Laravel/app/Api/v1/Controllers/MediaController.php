<?php

namespace App\Api\v1\Controllers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;
use App\Api\v1\Interfaces\PinInterface;

class MediaController extends Controller implements PinInterface {
    use Helpers;
    
    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function create() {

    }

    public function update($media_id) {

    }

    public function getOne($media_id) {

    }

    public function delete($media_id) {

    }

    public function getFromUser($user_id) {

    }
}


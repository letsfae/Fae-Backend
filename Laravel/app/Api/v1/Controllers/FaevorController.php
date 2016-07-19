<?php

namespace App\Api\v1\Controllers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;
use App\Api\v1\Interfaces\PinInterface;

class FaevorController extends Controller implements PinInterface {
    use Helpers;
    
    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function create() {

    }

    public function update($faevor_id) {

    }

    public function getOne($faevor_id) {

    }

    public function delete($faevor_id) {

    }

    public function getFromUser($user_id) {

    }
}


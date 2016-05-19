<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;

class UserController extends Controller
{
    use Helpers;

    public function __construct(Request $request) {
    	$this->request = $request;
    }

    public function signUp() {
    	$jsonArray = $this->request->json()->all();
        print_r($jsonArray);
    }

    public function getProfile() {
    	echo 'In get profile';
    }
}

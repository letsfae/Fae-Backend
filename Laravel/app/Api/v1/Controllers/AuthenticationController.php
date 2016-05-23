<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Dingo\Api\Routing\Helpers;
use App\User;

class AuthenticationController extends Controller
{
    use Helpers;
    
    public function __construct(Request $request) {
    	$this->request = $request;
    }

    public function login() {
        // validation

        // generate token

        // create session in db (if exists, replace it)
        // record device_id...
    }

    public function logout() {
        // delete session with session_id
    }
}

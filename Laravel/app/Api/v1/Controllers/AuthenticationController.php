<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AuthController extends Controller
{
    public function __construct(Request $request) {
    	$this->request = $request;
    }

    public function login() {
    	echo 'login';
    }

    public function logout() {
    	echo 'logout';
    }
}

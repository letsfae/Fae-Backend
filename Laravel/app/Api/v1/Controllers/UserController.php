<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

// use App\Http\Requests;

class UserController extends Controller
{
    public function __construct(Request $request) {
    	$this->request = $request;
    }

    public function signUp() {
    	$jsonArray = $this->request->json()->all();
        print_r($jsonArray);
    }
}

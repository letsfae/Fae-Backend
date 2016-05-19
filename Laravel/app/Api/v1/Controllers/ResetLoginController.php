<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;


// use App\Http\Requests;

class ResetLoginController extends Controller
{
    use Helpers;

    public function __construct(Request $request) {
    	$this->request = $request;
    }

    public function sendResetCode() {

    }

    public function verifyResetCode() {

    }
}

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
        // validation

        // generate code & store user_id and code to verification table

        // send code to this email
    }

    public function verifyResetCode() {
        // validation

        // compare user_id and code with data in verification table
    }

    public function resetPassword() {
        
    }
}

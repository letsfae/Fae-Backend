<?php

namespace App\Api\v1\Controllers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;

class FaevorController extends Controller {
    use Helpers;
    
    public function __construct(Request $request) {
        $this->request = $request;
    }
}


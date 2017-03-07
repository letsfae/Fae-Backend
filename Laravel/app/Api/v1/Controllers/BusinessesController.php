<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;

class BusinessController extends Controller implements {
    use Helpers;
    
    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function getOne($business_id) {

    }

    public function getImage($business_id) {
        
    }
}

<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;

class MapController extends Controller {
    use Helpers;
    
    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function getMap() {
    	
    }

    public function updateUserLocation() {

    }

    public function setActive() {

    }

    public function getActive() {

    }
}

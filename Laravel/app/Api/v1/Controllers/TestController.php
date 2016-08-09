<?php
namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Davibennun\LaravelPushNotification\Facades\PushNotification;

class TestController extends Controller {
    use Helpers;
    
    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function sendPushNotification() {
        
    }
}

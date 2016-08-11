<?php
namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Davibennun\LaravelPushNotification\Facades\PushNotification;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Dingo\Api\Routing\Helpers;
use Validator;

class TestController extends Controller {
    use Helpers;
    
    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function sendPushNotification() {
        // validation
    	$input = $this->request->all();
        $validator = Validator::make($input, [
            'device_id' => 'required|string'
        ]);
        
        if($validator->fails()){
    		throw new AccessDeniedHttpException('Please input device id.');
    	}
        
        
        $device_id_input = $this->request->device_id;
        $message = PushNotification::Message('Other device is loging in', array(
            'badge' => 1,
            'custom' => array('custom data' => array(
                'type' => 'authentication_other_device',
                'auth' => false
            ))
        ));
        $collection = PushNotification::app('appNameIOS')
            ->to($device_id_input)
            ->send($message);
    }
}

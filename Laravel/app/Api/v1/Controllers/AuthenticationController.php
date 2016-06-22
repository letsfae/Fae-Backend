<?php

namespace App\Api\v1\Controllers;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Dingo\Api\Exception\DeleteResourceFailedException;
use Dingo\Api\Routing\Helpers;
use Auth;
use Validator;
use Config;
use App\Sessions;
use App\Users;
use App\Api\v1\Controllers\MapController;
use Davibennun\LaravelPushNotification\Facades\PushNotification;

class AuthenticationController extends Controller {
    use Helpers;
    
    public function __construct(Request $request) {
    	$this->request = $request;
    }

    public function login()
    {
        // validation
        AuthenticationController::loginValidation($this->request);
        $email = '';
        $user_name = '';
        if ($this->request->has('email'))
        {
            $email = strtolower($this->request->email);
        }
        if ($this->request->has('user_name'))
        {
            $user_name = ($this->request->user_name);
        }
        $password = $this->request->password;
        if (!empty($user_name) && empty($email))
        {
            $user_table = Users::where('user_name', $user_name)->first();
            $email = $user_table->email;
        }
        $credentials = [
            'email' => $email,
            'password' => $password
        ];
        $users = Users::where('email', '=', $email)->first();
        if ($users == null) {
            throw new AccessDeniedHttpException('Bad request, No such users exist!');
        }
        
        $is_mobile = false;
        $device_id = '';
        if ($this->request->has('is_mobile')){
            $is_mobile_input = array('is_mobile' => $this->request->is_mobile);
            $is_mobile_validator = Validator::make($is_mobile_input, [
                'is_mobile' => 'required|boolean',
            ]);
            if($is_mobile_validator->fails()) {            
                 throw new AccessDeniedHttpException('Bad request, is_mobile value is wrong!');             
            }
            $is_mobile = ($this->request->is_mobile);
        }
        if ($is_mobile == true){
            $device_id_input = array('device_id' => $this->request->device_id);
            $device_id_validator = Validator::make($device_id_input, [
                'device_id' => 'required|between:1,150',
            ]);
            if($device_id_validator->fails()) {            
                 throw new AccessDeniedHttpException('Bad request, device id is wrong!');             
            }
            $device_id = $this->request->device_id;
        }
        
        
        if (Auth::attempt($credentials)) {  
            $token = str_random(30);
            $client_version = $this->request->header('Fae-Client-Version'); 
            $input = array(/*'device_id' => $device_id, */'client_version' => $client_version);
            $validator = Validator::make($input, [
                //'device_id' => 'required|max:200',
                'client_version' => 'required|max:50',
            ]);
            if($validator->fails()) {            
                 throw new AccessDeniedHttpException('Bad request, Please verify your input header!');             
            }
            $user_id = $users->id;
            //create session in db (if exists, replace it)
            //------------------------------------
            //$session_back = Sessions::where('device_id', $device_id)->first();
            //$session_active = Sessions::where('user_id', $user_id)->where('active', true)->first();
            $session_back = Sessions::where('user_id', $user_id)->first();
            $session_id = '';
            if ($session_back != null)
            {
                if(Config::get('app.pushback')==true) {
                    AuthenticationController::pushNotificationCurrentUser($session_back->device_id, $device_id, $client_version);
                }
                
                $session_back->token = $token;
                //$session_back->user_id = $user_id;
                if ($is_mobile == true){
                    $session_back->is_mobile = $is_mobile;
                    $session_back->device_id = $device_id;
                }
                $session_back->client_version = $client_version;
                $session_back->save();
                //MapController::setUserActive($session_back->id);
                $session_id = $session_back->id;
                //session does not exist
            }
            else
            {
                //---------------------------------
                $session = new Sessions();
                $session->user_id = $user_id;
                $session->token = $token;
                if ($is_mobile == true){
                    $session->is_mobile = $is_mobile;
                    $session->device_id = $device_id;
                }                
                $session->client_version = $client_version;
                $session->save();
                //MapController::setUserActive($session->id);
                $session_id = $session->id;
            }
            $users->login_count = 0;
            $content = array('user_id' => $user_id, 'token' => $token, 'session_id' => $session_id);
            if(Config::get('app.debug')) {
                $content['debug_base64ed'] = base64_encode($user_id.':'.$token.':'.$session_id);
            }
            return $this->response->created(null, $content);
        } 
        else {
            if ($users == null)
            {
                throw new AccessDeniedHttpException('Bad request, Please verify your information!');  
            }
            $login_count = $users->login_count + 1;
            $users->login_count = $login_count;
            $users->save();
            //forbid user when login time over 3;
            if ($login_count > '3')
            {
                throw new AccessDeniedHttpException('You have tried to login '.$login_count.' times, please change your password!');
            }
            else
            {
                throw new AccessDeniedHttpException('Bad request, Please verify your information!');  
            }
        }
    }
        
    private function loginValidation(Request $request)
    {
        $input = $request->all();
        if($request->has('email'))
        {
            $input['email'] = strtolower($input['email']);
        }
        $validator = Validator::make($input, [
            'email' => 'required_without:user_name|max:50|email',
            'user_name' => 'required_without:email|max:50',
            'password' => 'required|between:8,16',
        ]);
        if($validator->fails())
        {            
            throw new AccessDeniedHttpException('Bad request, Please verify your information!');             
        }
    }
    
   private function pushNotificationCurrentUser($previous_device_id, $device_id, $fae_client_version){
        $auth = ($previous_device_id != '');
        $message = PushNotification::Message('Other device is loging in',array(
            'type' => 'authentication_other_device',
            'device_id' => $device_id,
            'fae_client_version' => $fae_client_version,
            'auth' => $auth
        ));

        $collection = PushNotification::app('appNameIOS')
            ->to($previous_device_id)
            ->send($message);
        // get response for each device push
        foreach ($collection->pushManager as $push) {
            $response = $push->getAdapter()->getResponse();
        }
    }

    public function logout()
    {
        $session = Sessions::find($this->request->self_session_id);
        Auth::logout();
        $flag = $session->active;
        $session->delete();
        $new_session = Sessions::where('user_id', $this->request->self_user_id)->first();
        if(!is_null($new_session) && $flag == true)
        {
            $new_session->active = true;
            $new_session->save();
        }
        return $this->response->noContent();
    }
}

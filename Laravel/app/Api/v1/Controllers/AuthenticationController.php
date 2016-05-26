<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Dingo\Api\Exception\DeleteResourceFailedException;
use Dingo\Api\Routing\Helpers;
use Illuminate\Support\Facades\Hash;
use Auth;
use Validator;
use App\Sessions;
use App\Users;

class AuthenticationController extends Controller {
    use Helpers;
    
    public function __construct(Request $request) {
    	$this->request = $request;
    }

    public function login() {
        // validation
        AuthenticationController::loginValidation($this->request);
        $email = '';
        $user_name = '';
        echo $email;
        if ($this->request->has('email')) {
            $email = strtolower($this->request->email);
        }
        if ($this->request->has('user_name')) {
            $user_name = ($this->request->user_name);
        }
        $password = $this->request->password;
        if (!empty($user_name) && empty($email)) {
            $user_table = Users::where('user_name', '=', $user_name)->first();
            $email = $user_table->email;
        }
        $credentials = [
                'email' => $email,
                'password' => $password
        ];
        $users = Users::where('email', '=', $email)->first();
        if (Auth::attempt($credentials)) {  
            $token = str_random(30);
            $device_id = $this->request->header('Device_ID');
            $client_version = $this->request->header('Fae-Client-Version'); 
            $user_id = $users->id;
            //create session in db (if exists, replace it)
            $session_back = Sessions::where('device_id', '=', $device_id)->first();
            $session_id = '';
            if ($session_back != null) {
                $session_back->token = $token;
                $session_back->client_version = $client_version;
                $session_back->save();
                $session_id = $session_back->id;
            //session does not exist
            }
            else {
                $session = new Sessions();
                $session->user_id = $user_id;
                $session->token = $token;
                $session->device_id = $device_id;
                $session->client_version = $client_version;
                $session->save();
                $session_id = $session->id;
            }
            return $this->response->created($location = null, $content = array('user_id' => $user_id, 'token' => $token, 'session_id' => $session_id));
        } 
        else {
            if ($users == null) {
                throw new AccessDeniedHttpException('Bad request, Please verify your information!');  
            }
            $login_count = $users->login_count + 1;
            $users->login_count = $login_count;
            $users->save();
            //forbid user when login time over 3;
            if ($login_count > '3') {
                throw new AccessDeniedHttpException('You have tried to login '.$login_count.' times, please change your password!');
            }
            else {
                throw new AccessDeniedHttpException('Bad request, Please verify your information!');  
            }
        }
 
    }
        
    private function loginValidation(Request $request) {
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
        if($validator->fails()) {            
            throw new AccessDeniedHttpException('Bad request, Please verify your information!');             
        }
    }
    public function logout() {
        $user_id = '17';
        $session_id = '7';
        $session = Sessions::where('id', '=', $session_id)->where('user_id', '=', $user_id)->first(); 
        //if session does not exist
        if ($session == null) {
            throw new DeleteResourceFailedException('Delete failed');
        }
        //session exists
        else {
        Auth::logout();
        $session->delete();
        return $this->response->noContent();
        }  
    }
}

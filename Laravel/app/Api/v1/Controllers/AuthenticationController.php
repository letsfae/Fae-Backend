<?php
namespace App\Api\v1\Controllers;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Dingo\Api\Exception\DeleteResourceFailedException;
use Dingo\Api\Routing\Helpers;
use Validator;
use Config;
use App\User_exts;
use App\Sessions;
use App\Users;
use App\Api\v1\Controllers\MapController;
use App\Api\v1\Controllers\SyncController;
use Davibennun\LaravelPushNotification\Facades\PushNotification;
use App\Api\v1\Utilities\ErrorCodeUtility;
class AuthenticationController extends Controller {
    use Helpers;
    public function __construct(Request $request) {
        $this->request = $request;
    }
    public function login(){
        //------------------------------------ validation ------------------------------------
        AuthenticationController::loginValidation($this->request);
        
        // check user existance with email or username
        $email = '';
        $user_name = '';
        $users = null;
        if ($this->request->has('email')){
            $email = strtolower($this->request->email);
        }
        if ($this->request->has('user_name')){
            $user_name = ($this->request->user_name);
        }
        $password = $this->request->password;
        if (!empty($user_name) && empty($email)){
            $users = Users::where('user_name', 'ilike',$user_name)->first();
            if ($users == null) {
                return response()->json([
                    'message' => 'user not found',
                    'error_code' => ErrorCodeUtility::USER_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
            }
        } else {
            $users = Users::where('email', $email)->where('email_verified',true)->first();
            if(is_null($users)){
                return response()->json([
                        'message' => 'email not registered or not verified',
                        'error_code' => ErrorCodeUtility::EMAIL_NOT_VALID,
                        'status_code' => '401'
                    ], 401);
            }
        }
        //forbid user when login time over 3;
        if ($users->login_count >= 6){
            throw new AccessDeniedHttpException('You have tried to login '.($users->login_count).' times, please change your password!');
        }
        
        // check is_mobile and device_id
        $is_mobile = false;
        
        //(tempory)force the is_mobile to be true, so that each user only has one session
        $is_mobile = true;
        
        $device_id = null;
        if ($this->request->has('is_mobile')){
            $is_mobile = ($this->request->is_mobile == 'true');
//            $is_mobile_input = $this->request->is_mobile;
//            if ( $is_mobile_input == 'true' || $is_mobile_input == 'TRUE' || $is_mobile_input == 'True' || $is_mobile_input == '1'){
//                $is_mobile = true;
//            }else if ( $is_mobile_input == 'false' || $is_mobile_input == 'FALSE' || $is_mobile_input == 'False' || $is_mobile_input == '0'){
//                $is_mobile = false;
//            }else{
//                throw new AccessDeniedHttpException('Bad request, is_mobile is wrong!');
//            }
        }
        
        if ($this->request->has('device_id')){
            $device_id_input = array('device_id' => $this->request->device_id);
            $device_id_validator = Validator::make($device_id_input, [
                'device_id' => 'required|between:1,150',
            ]);
            if($device_id_validator->fails()) {            
                 throw new AccessDeniedHttpException('Bad request, device id is wrong!');             
            }
            $device_id = $this->request->device_id;
        }
        
        //------------------------------------ check password and attempt login ------------------------------------
        $password_right = Hash::check($this->request->password, $users->password);
        if (!$password_right){
            $login_count = $users->login_count + 1;
            $users->login_count = $login_count;
            $users->save();
            
            return response()->json([
                'message' => 'Bad request, Password incorrect!',
                'error_code' => ErrorCodeUtility::INCORRECT_PASSWORD,
                'status_code' => '401',
                'login_count' => $users->login_count
            ], 401);
            
            //throw new AccessDeniedHttpException('Bad request, Password incorrect!');  
        }
        $user_id = $users->id;
        
        //------------------------------------ store session ------------------------------------
        // check header
        $client_version = $this->request->header('Fae-Client-Version'); 
        $input = array('client_version' => $client_version);
        $validator = Validator::make($input, [
            'client_version' => 'required|max:50',
        ]);
        if($validator->fails()) {            
             throw new AccessDeniedHttpException('Bad request, Please verify your input header!');             
        }
        
        
        $token = str_random(30);
        $session_id = -1;
        $previous_sessions = Sessions::where('user_id', $user_id)->get();
        $previous_mobile_index = -1;
        foreach($previous_sessions as $key=>$value) {
            if( $value->is_mobile ){
                $previous_mobile_index = $key;
            }
            if( $value->is_mobile && $is_mobile){
                if(!empty($value->device_id) && $value->device_id != $device_id ){
                    if(Config::get('app.pushback')==true && !empty($value->device_id)) {
                        AuthenticationController::pushNotificationCurrentUser($value->device_id, $device_id, $client_version, true);
                    }
                }
            }
            if( ($value->is_mobile && $is_mobile) || ($device_id != null && !empty($value->device_id) && $value->device_id == $device_id   ) ){
                $value->token = $token;
                $value->is_mobile = $is_mobile;
                $value->device_id = $device_id;
                $value->client_version = $client_version;
                $value->save();
                $session_id = $value->id;
            }
        }
        if ($session_id == -1){
            if($previous_mobile_index != -1){
                if(Config::get('app.pushback')==true && $previous_sessions[$previous_mobile_index]->device_id != -1 ) {
                    AuthenticationController::pushNotificationCurrentUser($previous_sessions[$previous_mobile_index]->device_id, $device_id, $client_version, false);
                }
            }
            
            //create session in DB
            $session = new Sessions();
            $session->user_id = $user_id;
            $session->token = $token;
            $session->is_mobile = $is_mobile;
            $session->device_id = $device_id;
            
            $session->client_version = $client_version;
            $session->save();
            $session_id = $session->id;
        }
        $users->login_count = 0;
        $users->save();
        
        
        $user_exts = User_exts::find($user_id);
        if ( $user_exts != null ){
            if($user_exts->status ==0){
                $user_exts->status = 1;
            }
            $user_exts->save();
        }
        
        //------------------------------------ printout result ------------------------------------
        $content = array('user_id' => $user_id, 'token' => $token, 'session_id' => $session_id, 
                         'last_login_at' => $users->last_login_at);
        //var_dump($content);
        if(Config::get('app.debug')) {
            $content['debug_base64ed'] = base64_encode($user_id.':'.$token.':'.$session_id);
        }
        //var_dump($content);
        return $this->response->created(null, $content);
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
            'is_mobile' => 'in:true,false',
        ]);
        if($validator->fails())
        {            
            throw new AccessDeniedHttpException('Bad request, Please verify your information!');             
        }
    }
    
   private function pushNotificationCurrentUser($previous_device_id, $device_id, $fae_client_version, $both_are_mobile){
        $message = PushNotification::Message('Other device is loging in', array(
             'badge' => 1,
             'custom' => array('custom data' => array(
                'type' => 'authentication_other_device',
                'device_id' => $device_id,
                'fae_client_version' => $fae_client_version,
                'auth' => !$both_are_mobile
             ))
        ));
        try{
            $collection = PushNotification::app('appNameIOS')
                                                ->to($previous_device_id)
                                                ->send($message);
            // get response for each device push
            foreach ($collection->pushManager as $push) {
                $response = $push->getAdapter()->getResponse();
            }
        }catch(\Exception $e){
           
        }
        
    }
    public function logout()
    {
        $session = Sessions::find($this->request->self_session_id);
        $user = Users::find($this->request->self_user_id);
        if($session!= null){
            $user_id = $session->user_id;
            $user->last_login_at = $session->created_at;
            $user->save();
            $session->delete();
            
            $remaining_sessions = Sessions::where('user_id', $user_id)->first();
            if( $remaining_sessions == null){
                 $user_exts = User_exts::find($user_id);
                 if ( $user_exts != null ){
                    $user_exts->status = 0;
                    $user_exts->save();
                 }
            }
        }
        return $this->response->noContent();
    }

    public function guestLogin(){
        // check header
        $client_version = $this->request->header('Fae-Client-Version'); 
        $input = array('client_version' => $client_version);
        $validator = Validator::make($input, [
            'client_version' => 'required|max:50',
        ]);
        if($validator->fails()) {            
             throw new AccessDeniedHttpException('Bad request, Please verify your input client_version header!');             
        }

        $user_id= -1;
        $token = str_random(30);

        //create session in DB
        $session = new Sessions();
        $session->token = $token;
        $session->is_mobile = false;
        $session->device_id = null;
        
        $session->client_version = $client_version;
        $session->save();
        $session_id = $session->id;


        $content = array('token' => $token, 'session_id' => $session_id);
        if(Config::get('app.debug')) {
            $content['debug_base64ed'] = base64_encode($user_id.':'.$token.':'.$session_id);
        }
        return $this->response->created(null, $content);
    }
}

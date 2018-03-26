<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Validator;
use Illuminate\Support\Facades\Hash;
use App\Users;
use App\User_exts;
use App\UserSettings;
use App\Sessions;
use App\Verifications;
use App\Friends;
use App\Friend_requests;
use App\Follows;
use App\Blocks;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Exception\UpdateResourceFailedException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Dingo\Api\Routing\Helpers;
use App\Name_cards;
use App\Chats;
use App\Api\v1\Controllers\CollectionController;
use Mail;
use Twilio;
use DateTime;
use App\Api\v1\Utilities\ErrorCodeUtility;
use Firebase\Firebase;

class UserController extends Controller
{
    use Helpers;

    public function __construct(Request $request)
    {
    	$this->request = $request;
    }

    public function signUp() 
    {
        UserController::signUpValidation($this->request);
        if(Users::whereRaw('LOWER(user_name) = ?', [strtolower($this->request->user_name)])->exists())
        {
            return response()->json([
                    'message' => 'user name already exists',
                    'error_code' => ErrorCodeUtility::USER_NAME_ALREADY_EXISTS,
                    'status_code' => '422'
                ], 422);
        }
        $user = new Users;
        if($this->request->has('email')) {
            $email = strtolower($this->request->email);
            if(Users::where('email',$email)->where('email_verified',true)->exists()) {
                return response()->json([
                    'message' => 'email already exists',
                    'error_code' => ErrorCodeUtility::EMAIL_ALREADY_EXISTS,
                    'status_code' => '422'
                ], 422);
            }
            $user->email = $email;
        }
        $user->user_name = $this->request->user_name;
        $user->password = bcrypt($this->request->password);
        $user->first_name = $this->request->first_name;
        $user->last_name = $this->request->last_name;
        $user->gender = $this->request->gender;
        $user->birthday = $this->request->birthday;
        $user->save();
        $user_exts = new User_exts;
        $user_exts->user_id = $user->id;
        $user_exts->save();
        $user_setting = new UserSettings;
        $user_setting->user_id = $user->id;
        $user_setting->save();
        $nameCard = new Name_cards;
        $nameCard->user_id = $user->id;
        $nameCard->save();

        $fb = Firebase::initialize('https://faeapp-5ea31.firebaseio.com', 'LiYqgdzrv8Y1s2lRN7iziHy4Z5UCgvUlJJhHcZRe');
        $dateTime = new DateTime();
        $fb->push('Message-dev/1-'.$user->id, 
                    array('message' => 'Hey there! Welcome to the Early Bird Version of Fae Map!',
                          'type' => 'text',
                          'senderId' => '1',
                          'senderName' => 'Fae Map Crew',
                          'messageId' => uniqid(),
                          'hasTimeStamp' => true,
                          'index' => 1,
                          'status' => 'Delivered',
                          'date' => $dateTime->format('Ymdhis'))
                  );
        $fb->push('Message-dev/1-'.$user->id, 
                    array('message' => 'We’re here to enhance your experience and ensure you have a great time on our platform. Kindly let us know if you encounter any problems or what we can do to make your experience better. Let’s Fae!',
                          'type' => 'text',
                          'senderId' => '1',
                          'senderName' => 'Fae Map Crew',
                          'messageId' => uniqid(),
                          'hasTimeStamp' => true,
                          'index' => 2,
                          'status' => 'Delivered',
                          'date' => $dateTime->format('Ymdhis'))
                  );
        $chat = new Chats;
        $chat->user_a_id = 1;
        $chat->user_b_id = $user->id;
        $chat->last_message = 'We’re here to enhance your experience and ensure you have a great time on our platform. Kindly let us know if you encounter any problems or what we can do to make your experience better. Let’s Fae!';
        $chat->last_message_type = 'text';
        $chat->last_message_sender_id = 1;
        $chat->updateTimestamp();
        $chat->user_b_unread_count++;
        $chat->save();

        CollectionController::createDefaultCollections($user->id);

        return $this->response->created();
    }

    public function updateAccount() 
    {
        $this->updateAccountValidation($this->request);
        $user = Users::find($this->request->self_user_id);
        if($this->request->has('first_name'))
        {
            $user->first_name = $this->request->first_name;
        }
        if($this->request->has('last_name'))
        {
            $user->last_name = $this->request->last_name;
        }
        if($this->request->has('gender'))
        {
            $user->gender = $this->request->gender;
        }
        if($this->request->has('birthday'))
        {
            $user->birthday = $this->request->birthday;
        }
        if($this->request->has('user_name'))
        {
            if(Users::where('user_name', 'ilike', $this->request->user_name)->exists())
            {
                return response()->json([
                    'message' => 'user name already exists',
                    'error_code' => ErrorCodeUtility::USER_NAME_ALREADY_EXISTS,
                    'status_code' => '422'
                ], 422);
            }
            $user->user_name = $this->request->user_name;
        }
        if($this->request->has('mini_avatar'))
        {
            $user->mini_avatar = $this->request->mini_avatar;
        }
        $user->save();
        return $this->response->created();
    }

    public function getAccount() 
    {
        $user = Users::find($this->request->self_user_id);
        if(! is_null($user))
        {
            $account = array(
                'email' => $user->email,
                'email_verified' => $user->email_verified,
                'user_name' => $user->user_name,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'gender' => $user->gender,
                'birthday' => $user->birthday,
                'phone' => $user->phone,
                'phone_verified' => $user->phone_verified,
                'mini_avatar' => $user->mini_avatar,
                'last_login_at' => $user->last_login_at
            );
            return $this->response->array($account);
        }
        return response()->json([
                    'message' => 'user not found',
                    'error_code' => ErrorCodeUtility::USER_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
    }

    public function updatePassword() 
    {
        $this->updatePasswordValidation($this->request);
        $user = $user = Users::find($this->request->self_user_id);
        $password_right = Hash::check($this->request->old_password, $user->password);
        if (!$password_right)
        {
            $user->login_count++;
            $user->save();
            if($user->login_count >= 6)
            {
                $session = Sessions::find($this->request->self_session_id);
                $session->delete();
                return response()->json([
                    'message' => 'Incorrect password, automatically lougout',
                    'error_code' => ErrorCodeUtility::INCORRECT_PASSWORD,
                    'status_code' => '401'
                ], 401);
            }
            return response()->json([
                    'message' => 'Incorrect password, you still have '.(6-$user->login_count).' chances',
                    'error_code' => ErrorCodeUtility::INCORRECT_PASSWORD,
                    'status_code' => '401',
                    'login_count' => $user->login_count
                ], 401);
            
            //throw new UnauthorizedHttpException('Incorrect password, you still have '.(3-$user->login_count).' chances');
        }
        $user->password = bcrypt($this->request->new_password);
        $user->login_count = 0;
        $user->save();
        return $this->response->created();
    }

    public function verifyPassword() 
    {
        $validator = Validator::make($this->request->all(), [
            'password' => 'required|string',
        ]);
        if($validator->fails()){
            throw new StoreResourceFailedException('Could not verify password.',$validator->errors());
        }
        $user = Users::find($this->request->self_user_id);
        $password_right = Hash::check($this->request->password, $user->password);
        if (!$password_right)
        {
            $user->login_count++;
            $user->save();
            if($user->login_count >= 6)
            {
                $session = Sessions::find($this->request->self_session_id);
                $session->delete();
                return response()->json([
                    'message' => 'Incorrect password, automatically lougout',
                    'error_code' => ErrorCodeUtility::INCORRECT_PASSWORD,
                    'status_code' => '401'
                ], 401);
            }
            return response()->json([
                'message' => 'Incorrect password, you still have '.(6-$user->login_count).' chances',
                'error_code' => ErrorCodeUtility::INCORRECT_PASSWORD,
                'status_code' => '401',
                'login_count' => $user->login_count
            ], 401);
        }
        $user->login_count = 0;
        $user->save();
        return $this->response->created();
    }

    public function updateSelfStatus() 
    {
        $this->updateSelfStatusValidation($this->request);
        $user_exts = User_exts::find($this->request->self_user_id);
        if($this->request->has('status'))
        {
            $user_exts->status = $this->request->status;
        }
        if($this->request->has('message'))
        {
            $user_exts->message = $this->request->message;
        }
        if(!is_null($this->request->message) && empty($this->request->message))
        {
            $user_exts->message = null;
        }
        $user_exts->save();
        return $this->response->created();
    }    

    public function getSelfStatus() 
    {
        return $this->getStatus($this->request->self_user_id);
    }

    public function getStatus($user_id) 
    {
        $user_exts = User_exts::find($user_id);
        if(is_null($user_exts))
        {
            return response()->json([
                    'message' => 'user not found',
                    'error_code' => ErrorCodeUtility::USER_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        if($user_id != $this->request->self_user_id && $user_exts->status == 5)
        {
            $info[] = ['status' => 0, 'message' => $user_exts->message];
        }
        else
        {
            $info[] = ['status' => $user_exts->status, 'message' => $user_exts->message];
        }
        return $this->response->array($info);
    }

    private function signUpValidation(Request $request)
    {
        $input = $request->all();
        if($request->has('email'))
        {
            $input['email'] = strtolower($input['email']);
        }
        $validator = Validator::make($input, [
            'email' => 'filled|max:50|email',
            'user_name' => 'required|unique:users,user_name|regex:/^[a-zA-Z0-9]*[_\-.]?[a-zA-Z0-9]*$/|min:3|max:20',
            'password' => 'required|between:8,16',
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'gender' => 'required|in:male,female',
            'birthday' => 'required|date_format:Y-m-d|before:tomorrow|after:1900-00-00',
        ]);
        if($validator->fails())
        {
            throw new StoreResourceFailedException('Could not create new user.',$validator->errors());
        }
    }

    private function updateAccountValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'filled|required_without_all:last_name,gender,birthday,user_name,mini_avatar|string|max:50',
            'last_name' => 'filled|required_without_all:first_name,gender,birthday,user_name,mini_avatar|string|max:50',
            'gender' => 'filled|required_without_all:last_name,first_name,birthday,user_name,mini_avatar|in:male,female',
            'birthday' => 'filled|required_without_all:last_name,gender,first_name,user_name,mini_avatar|
                           date_format:Y-m-d|before:tomorrow|after:1900-00-00',
            'user_name' => 'filled|required_without_all:last_name,gender,birthday,first_name,mini_avatar|regex:/^[a-zA-Z0-9]*[_\-.]?[a-zA-Z0-9]*$/|min:3|max:20',
            'mini_avatar' => 'filled|required_without_all:last_name,gender,birthday,user_name,first_name|integer|between:0,100',
        ]);
        if($validator->fails())
        {
            throw new UpdateResourceFailedException('Could not update user account.',$validator->errors());
        }
    }

    private function updatePasswordValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string|between:8,16',
            'new_password' => 'required|string|between:8,16',
        ]);
        if($validator->fails())
        {
            throw new UpdateResourceFailedException('Could not update user password.',$validator->errors());
        }
    }

    private function updateSelfStatusValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'filled|required_without:message|integer|between:0,5',
            'message' => 'required_without:status|string|max:100',
        ]);


        if($validator->fails())
        {
            if(!is_null($request->message) && empty($request->message))
            {
                if($request->has('status'))
                {
                    $validator = Validator::make($request->all(), [
                        'status' => 'integer|between:0,5'
                    ]);
                    if($validator->fails())
                    {
                        throw new UpdateResourceFailedException('Could not update user status.',$validator->errors());
                    }
                }
                return;
            }
           
            throw new UpdateResourceFailedException('Could not update user status.',$validator->errors());
        }
    }

    public function updateEmail() {
        // validation
        $validator = Validator::make($this->request->all(), [
            'email' => 'required|max:50|email'
        ]);
        if($validator->fails())
    	{
    		throw new StoreResourceFailedException('Could not verify.',$validator->errors());
    	}

        $email = strtolower($this->request->email);
        if(Users::where('email',$email)->where('email_verified',true)->exists()) {
            return response()->json([
                'message' => 'email already exists',
                'error_code' => ErrorCodeUtility::EMAIL_ALREADY_EXISTS,
                'status_code' => '422'
            ], 422);
        }
        
        // generate code & store user_id and code to verification table
        $verification_code = (string)rand(111111,999999);
        $verification = Verifications::where('email','=', $email)->where('type','=', 'email')->get();
        if( is_null($verification) || ( count($verification)==0 ) ){
            $new_verification = new Verifications;
            $new_verification->type = 'email';
            $new_verification->email = $email;
            $new_verification->code = $verification_code;
            $new_verification->save();
        }else{
            if ($verification[0]->created_at->diffInMinutes()>30) {
                $verification[0]->delete();
                
                $new_verification = new Verifications;
                $new_verification->type = 'email';
                $new_verification->email = $email;
                $new_verification->code = $verification_code;
                $new_verification->save();
            }else{
                $verification_code = $verification[0]->code;
            }
        }
        
        $email_to_send = "Hello!\n\n";
        $email_to_send = $email_to_send."This is Fae Support. Below is the code for updating your email. Please enter the code on your device and proceed to creating your new password. The code will be valid for 3 hours and when a new one is issued.\n";
        $email_to_send = $email_to_send."\n".$verification_code."\n\n";
        $email_to_send = $email_to_send."Have a wonderful day!\n";
        $email_to_send = $email_to_send."Fae Support\n\n";
        $email_to_send = $email_to_send."*If you did not request this change then please ignore the email.\n";
        
        
        
        
        // send code to this email
        $user = Users::find($this->request->self_user_id);
        $user->email = $email;
        $user->email_verified = false;
        $user->save();
        
        Mail::raw($email_to_send, function ($message) {
            $message->from('support@letsfae.com', 'Fae Support');

            $message->to($this->request->email)->subject('Fae-Update Email verification');
        });

        return $this->response->created();
    }

    public function verifyEmail() {
        // validation
        $input = $this->request->all();
        if($this->request->has('email')){
    	    $input['email'] = strtolower($input['email']);
        }
        
        $validator = Validator::make($input, [
            'email' => 'required|max:50|email',
            'code' => 'required|string|max:6'
        ]);
        
        if($validator->fails())
    	{
    		throw new StoreResourceFailedException('Could not update email.',$validator->errors());
    	}
        
        // compare user_id and code with data in verification table
        $verification = Verifications::where('email','=', $input['email'])->where('type','=', 'email')->get();
        if( is_null($verification) || ( count($verification)==0 ) ){
            return response()->json([
                    'message' => 'verification not found',
                    'error_code' => ErrorCodeUtility::VERIFICATION_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }else{
            if($verification[0]->code == $input['code']){
                if ($verification[0]->created_at->diffInMinutes()>30)
                    return $this->response->errorNotFound();
                $verification[0]->delete();
                
                //$user = Users::where('email','=',$input['email'])->get();
                $user = Users::find($this->request->self_user_id);
                $user->email = $input['email'];
                $user->email_verified = true;
                $user->save();

                $users = Users::where('email',$input['email'])->where('id','!=',$this->request->self_user_id)->where('email_verified',true)->get();
                foreach ($users as $user) {
                    $user->email_verified = false;
                    $user->save();
                }
                
                return $this->response->created();
            }else{
                return response()->json([
                    'message' => 'verification code is wrong',
                    'error_code' => ErrorCodeUtility::VERIFICATION_WRONG_CODE,
                    'status_code' => '403'
                ], 403);
            }
        }
    }

    public function updatePhone() {
        $validator = Validator::make($this->request->all(), [
            'phone' => 'required|max:20|regex:/^\([0-9]+\)[0-9]+$/'
        ]);
        
        if($validator->fails())
    	{
    		throw new StoreResourceFailedException('Could not verify.',$validator->errors());
    	}
        
        // generate code & store user_id and code to verification table
        $verification_code = (string)rand(111111,999999);
        $verification = Verifications::where('phone', $this->request->phone)->where('type', 'phone')->first();
        if( is_null($verification) ){
            $new_verification = new Verifications;
            $new_verification->type = 'phone';
            $new_verification->phone = $this->request->phone;
            $new_verification->code = $verification_code;
            $new_verification->save();
        }else{
            if ($verification->created_at->diffInMinutes()>30) {
                $verification->code = $verification_code;
                $verification->save();
            }else{
                $verification_code = $verification->code;
            }
        }

        Twilio::message($this->request->phone, $verification_code);
        
        return $this->response->created();        
    }

    public function verifyPhone() {
        $validator = Validator::make($this->request->all(), [
            'phone' => 'required|max:20|regex:/^\([0-9]+\)[0-9]+$/',
            'code' => 'required|string|max:6'
        ]);
        
        if($validator->fails())
    	{
    		throw new StoreResourceFailedException('Could not update phone.',$validator->errors());
    	}
        
        // compare user_id and code with data in verification table
        $verification = Verifications::where('phone', $this->request->phone)->where('type', 'phone')->first();
        if( is_null($verification)){
            return response()->json([
                    'message' => 'verification not found',
                    'error_code' => ErrorCodeUtility::VERIFICATION_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }else{
            if($verification->code == $this->request->code){
                if ($verification->created_at->diffInMinutes()>30){
                    return response()->json([
                        'message' => 'verification timeout',
                        'error_code' => ErrorCodeUtility::VERIFICATION_TIMEOUT,
                        'status_code' => '403'
                    ], 403);
                }
                $verification->delete();
                $user = Users::where('phone', $this->request->phone)->first();
                if(!is_null($user)) {
                    $user->phone = null;
                    $user->phone_verified = false;
                }
                $user = Users::find($this->request->self_user_id);
                $user->phone = $this->request->phone;
                $user->phone_verified = true;
                $user->save();
                return $this->response->created();
            }else{
                return response()->json([
                    'message' => 'verification code is wrong',
                    'error_code' => ErrorCodeUtility::VERIFICATION_WRONG_CODE,
                    'status_code' => '403'
                ], 403);
            }
        }
    }

    public function deletePhone() {
        $user = Users::find($this->request->self_user_id);
        $user->phone = null;
        $user->phone_verified = false;
        $user->save();
    }

    public function getRelation($user_id) {
        return $this->response->array($this->getRelationBetween($this->request->self_user_id, $user_id));
    }

    public static function getRelationBetween($user1, $user2) {
        $is_friend = Friends::where('user_id', $user1)->where('friend_id', $user2)->exists();
        $friend_requested = Friend_requests::where('user_id', $user1)
                                           ->where('requested_user_id', $user2)->exists();
        $friend_requested_by = Friend_requests::where('user_id', $user2)
                                              ->where('requested_user_id', $user1)->exists();
        $blocked = Blocks::where('user_id', $user1)->where('block_id', $user2)->exists();
        $blocked_by = Blocks::where('user_id', $user2)->where('block_id', $user1)->exists();
        $followed = Follows::where('user_id', $user1)->where('followee_id', $user2)->exists();
        $followed_by = Follows::where('user_id', $user2)->where('followee_id', $user1)->exists();
        return array("is_friend" => $is_friend, "friend_requested" => $friend_requested,
                     "friend_requested_by" => $friend_requested_by, "blocked" => $blocked,
                     "blocked_by" => $blocked_by, "followed" => $followed, "followed_by" => $followed_by);
    }

}

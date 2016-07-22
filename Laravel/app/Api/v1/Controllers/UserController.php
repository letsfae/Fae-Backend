<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Validator;
use Illuminate\Support\Facades\Hash;
use App\Users;
use App\User_exts;
use App\Sessions;
use App\Verifications;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Exception\UpdateResourceFailedException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Dingo\Api\Routing\Helpers;
use App\Name_cards;
use Mail;
use Twilio;

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
        $user = new Users;
        $user->email = strtolower($this->request->email);
        $user->password = bcrypt($this->request->password);
        $user->first_name = $this->request->first_name;
        $user->last_name = $this->request->last_name;
        $user->gender = $this->request->gender;
        $user->birthday = $this->request->birthday;
        $user->save();
        $user_exts = new User_exts;
        $user_exts->user_id = $user->id;
        $user_exts->save();
        $nameCard = new Name_cards;
        $nameCard->user_id = $user->id;
        $nameCard->save();
        return $this->response->created();
    }

    public function updateAccount() 
    {
        $this->updateAccountValidation($this->request);
        $flag = true;
        $user = Users::find($this->request->self_user_id);
        if($this->request->has('first_name'))
        {
            $flag = false;
            $user->first_name = $this->request->first_name;
        }
        if($this->request->has('last_name'))
        {
            $flag = false;
            $user->last_name = $this->request->last_name;
        }
        if($this->request->has('gender'))
        {
            $flag = false;
            $user->gender = $this->request->gender;
        }
        if($this->request->has('birthday'))
        {
            $flag = false;
            $user->birthday = $this->request->birthday;
        }
        if($this->request->has('user_name'))
        {
            $flag = false;
            $user->user_name = $this->request->user_name;
        }
        if($this->request->has('mini_avatar'))
        {
            $flag = false;
            $user->mini_avatar = $this->request->mini_avatar;
        }
        if($flag)
        {
            return $this->response->errorBadRequest();
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
                'email_verified' => $user->email_verfied,
                'user_name' => $user->user_name,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'gender' => $user->gender,
                'birthday' => $user->birthday,
                'phone' => $user->phone,
                'phone_verified' => $user->phone_verified,
                'mini_avatar' => $user->mini_avatar,
            );
            return $this->response->array($account);
        }
        return $this->response->errorNotFound();
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
            if($user->login_count >= 3)
            {
                $session = Sessions::find($this->request->self_session_id);
                $session->delete();
                throw new UnauthorizedHttpException(null, 'Incorrect password, automatically lougout');
            }
            
            return response()->json([
                'message' => 'Incorrect password, you still have '.(3-$user->login_count).' chances',
                'status_code' => 401,
                'login_count' => $user->login_count,
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
        if($this->request->has('password'))
        {
            $user = $user = Users::find($this->request->self_user_id);
            $password_right = Hash::check($this->request->password, $user->password);
            if (!$password_right)
            {
                $user->login_count++;
                $user->save();
                if($user->login_count >= 3)
                {
                    $session = Sessions::find($this->request->self_session_id);
                    $session->delete();
                    throw new UnauthorizedHttpException(null, 'Incorrect password, automatically lougout');
                }
                
                return response()->json([
                    'message' => 'Bad request, Password incorrect!',
                    'status_code' => 401,
                    'login_count' => $user->login_count,
                ], 401);
                //throw new UnauthorizedHttpException('Incorrect password, please verify your information!');
            }
            $user->login_count = 0;
            $user->save();
            return $this->response->created();
        }
        return $this->response->errorNotFound();
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
            return $this->response->errorNotFound();
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
            'email' => 'required|unique:users,email|max:50|email',
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
            'first_name' => 'filled|string|max:50',
            'last_name' => 'filled|string|max:50',
            'gender' => 'in:male,female',
            'birthday' => 'filled|date_format:Y-m-d|before:tomorrow|after:1900-00-00',
            'user_name' => 'filled|unique:users,user_name|regex:/^[a-zA-Z][a-zA-Z0-9_]{5,29}$/',
            'mini_avatar' => 'filled|integer|between:0,32',
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
        $input = $this->request->all();
        if($this->request->has('email')){
    	    $input['email'] = strtolower($input['email']);
        }
        
        $validator = Validator::make($input, [
            'email' => 'required|max:50|email'
        ]);
        
        if($validator->fails())
    	{
    		throw new StoreResourceFailedException('Could not verify.',$validator->errors());
    	}
        
        $user = Users::where('email','=',$input['email'])->get();
        if( ( count($user)!=0 ) ){
            $current_user = Users::find($this->request->self_user_id);
            if ($current_user->email != $user[0]->email){
                throw new AccessDeniedHttpException('Email already in use, please use another one!');
            }
        }
        
        // generate code & store user_id and code to verification table
        $verification_code = (string)rand(111111,999999);
        $verification = Verifications::where('email','=', $input['email'])->where('type','=', 'email')->get();
        if( is_null($verification) || ( count($verification)==0 ) ){
            $new_verification = new Verifications;
            $new_verification->type = 'email';
            $new_verification->email = $input['email'];
            $new_verification->code = $verification_code;
            $new_verification->save();
        }else{
            if ($verification[0]->created_at->diffInMinutes()>30) {
                $verification[0]->delete();
                
                $new_verification = new Verifications;
                $new_verification->type = 'email';
                $new_verification->email = $input['email'];
                $new_verification->code = $verification_code;
                $new_verification->save();
            }else{
                $verification_code = $verification[0]->code;
            }
        }
        
        // send code to this email
        $user = Users::find($this->request->self_user_id);
        $user->email = $input['email'];
        $user->email_verified = false;
        $user->save();
        
        Mail::raw($verification_code, function ($message) {
            $message->from('auto-reply@letsfae.com', 'Laravel');

            $message->to($this->request->email);
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
            return $this->response->errorNotFound();
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
                
                return $this->response->created();
            }else{
                return $this->response->errorNotFound();
            }
        }
    }

    public function updatePhone() {
        // validation
        $input = $this->request->all();
        
        $validator = Validator::make($input, [
            'phone' => 'required|max:20'
        ]);
        
        if($validator->fails())
    	{
    		throw new StoreResourceFailedException('Could not verify.',$validator->errors());
    	}
        
        $user = Users::where('phone','=',$input['phone'])->get();
        if( ( count($user)!=0 ) ){
            $current_user = Users::find($this->request->self_user_id);
            if ($current_user->phone != $user[0]->phone){
                throw new AccessDeniedHttpException('Phone number already in use, please use another one!');
            }
        }
        
        // generate code & store user_id and code to verification table
        $verification_code = (string)rand(111111,999999);
        $verification = Verifications::where('phone','=', $input['phone'])->where('type','=', 'phone')->get();
        if( is_null($verification) || ( count($verification)==0 ) ){
            $new_verification = new Verifications;
            $new_verification->type = 'phone';
            $new_verification->phone = $input['phone'];
            $new_verification->code = $verification_code;
            $new_verification->save();
        }else{
            if ($verification[0]->created_at->diffInMinutes()>30) {
                $verification[0]->delete();
                
                $new_verification = new Verifications;
                $new_verification->type = 'phone';
                $new_verification->phone = $input['phone'];
                $new_verification->code = $verification_code;
                $new_verification->save();
            }else{
                $verification_code = $verification[0]->code;
            }
        }
        
        // send code to this phone
        $user = Users::find($this->request->self_user_id);
        $user->phone = $input['phone'];
        $user->phone_verified = false;
        $user->save();
        
        Twilio::message($input['phone'], $verification_code);
        
        return $this->response->created();        
    }

    public function verifyPhone() {
        // validation
        $input = $this->request->all();
        
        $validator = Validator::make($input, [
            'phone' => 'required|max:20',
            'code' => 'required|string|max:6'
        ]);
        
        if($validator->fails())
    	{
    		throw new StoreResourceFailedException('Could not update phone.',$validator->errors());
    	}
        
        // compare user_id and code with data in verification table
        $verification = Verifications::where('phone','=', $input['phone'])->where('type','=', 'phone')->get();
        if( is_null($verification) || ( count($verification)==0 ) ){
            return $this->response->errorNotFound();
        }else{
            if($verification[0]->code == $input['code']){
                if ($verification[0]->created_at->diffInMinutes()>30)
                    return $this->response->errorNotFound();
                $verification[0]->delete();
                
                //$user = Users::where('email','=',$input['email'])->get();
                $user = Users::find($this->request->self_user_id);
                $user->phone = $input['phone'];
                $user->phone_verified = true;
                $user->save();
                
                return $this->response->created();
            }else{
                return $this->response->errorNotFound();
            }
        }
    }
}

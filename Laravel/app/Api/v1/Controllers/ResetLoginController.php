<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;

use Validator;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Dingo\Api\Exception\StoreResourceFailedException;
use App\Users;
use App\Verifications;
use App\Api\v1\Utilities\ErrorCodeUtility;

use Auth;
use Mail;
use Twilio;

use Carbon\Carbon;
// use App\Http\Requests;

class ResetLoginController extends Controller
{
    use Helpers;

    public function __construct(Request $request) {
    	$this->request = $request;
    }

    public function sendResetCode() {
        // validation
        $input = $this->request->all();
        if($this->request->has('email')){
    	    $input['email'] = strtolower($input['email']);
        }
        
        $validator = Validator::make($input, [
            'email' => 'required_without:phone|max:50|email',
            'phone' => 'required_without:email|max:20|regex:/^\([0-9]+\)[0-9]+$/',
            'user_name' => 'required_with:phone|regex:/^[a-zA-Z0-9]*[_\-.]?[a-zA-Z0-9]*$/|min:3|max:20',
        ]);
        
        if($validator->fails())
    	{
    		throw new StoreResourceFailedException('Could not verify.',$validator->errors());
    	}
        
        $input_key = '';
        $input_value = '';
        $user = null;
        if($this->request->has('email')){
            $input_key = 'email';
            $input_value = $input['email'];
            $user = Users::where($input_key, '=', $input_value)->first();
        }else{
            $input_key = 'phone';
            $input_value = $input['phone'];
            $user = Users::where($input_key, '=', $input_value)->where('user_name', '=', $input['user_name'])->first();
        }
        if( is_null($user) ){
            return response()->json([
                    'message' => 'user not found',
                    'error_code' => ErrorCodeUtility::USER_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        
        // generate code & store user_id and code to verification table
        $verification_code = (string)rand(111111,999999);
        $verification = Verifications::where($input_key,'=', $input_value)->where('type', 'resetpassword')->first();
        if( is_null($verification) ){
            $new_verification = new Verifications;
            $new_verification->type = 'resetpassword';
            if( $input_key == 'email' ){
                $new_verification->email = $input_value;
            }else{
                $new_verification->phone = $input_value;
            }
            
            $new_verification->code = $verification_code;
            $new_verification->save();
        }else{
            if ($verification->created_at->diffInMinutes()>30) {
                $verification->delete();
                
                $new_verification = new Verifications;
                $new_verification->type = 'resetpassword';
                if( $input_key == 'email' ){
                    $new_verification->email = $input_value;
                }else{
                    $new_verification->phone = $input_value;
                }
                $new_verification->code = $verification_code;
                $new_verification->save();
            }else{
                $verification_code = $verification->code;
            }
        }
        

        if( $input_key == 'email' ){
            // send code to this email
            $email = "Hello!\n\n";
            $email = $email."This is Fae Support. Below is the code for resetting your password. Please enter the code on your device and proceed to creating your new password. The code is valid for 3 hours or when a new one is issued.\n";
            $email = $email."\n".$verification_code."\n\n";
            $email = $email."Have a wonderful day!\n";
            $email = $email."Fae Support\n\n";
            $email = $email."*If you did not request this change then please ignore the email.\n";
            
            Mail::raw($email, function ($message) {
                $message->from('support@letsfae.com', 'Fae Support');

                $message->to($this->request->email)->subject('Fae-Reset your password');
            });
        }else{
            Twilio::message($this->request->phone, $verification_code);
        }
        

        return $this->response->created();
        
    }

    public function verifyResetCode() {
        // validation
        $input = $this->request->all();
        if($this->request->has('email')){
    	    $input['email'] = strtolower($input['email']);
        }
        
        $validator = Validator::make($input, [
            'email' => 'required_without:phone|max:50|email',
            'phone' => 'required_without:email|max:20|regex:/^\([0-9]+\)[0-9]+$/',
            'user_name' => 'required_with:phone|regex:/^[a-zA-Z0-9]*[_\-.]?[a-zA-Z0-9]*$/|min:3|max:20',
            'code' => 'required|string|max:6'
        ]);
        
        if($validator->fails()){
    		throw new StoreResourceFailedException('Could not verify.',$validator->errors());
    	}

        $input_key = '';
        $input_value = '';
        $user = null;
        if($this->request->has('email')){
            $input_key = 'email';
            $input_value = $input['email'];
            $user = Users::where($input_key, '=', $input_value)->first();
        }else{
            $input_key = 'phone';
            $input_value = $input['phone'];
            $user = Users::where($input_key, '=', $input_value)->where('user_name', '=', $input['user_name'])->first();
        }

        if( is_null($user) ){
            return response()->json([
                    'message' => 'user not found',
                    'error_code' => ErrorCodeUtility::USER_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }

        // compare user_id and code with data in verification table
        $verification = Verifications::where($input_key,'=', $input_value)->where('type','=', 'resetpassword')->first();
        if( is_null($verification) ){
            return response()->json([
                    'message' => 'verification not found',
                    'error_code' => ErrorCodeUtility::VERIFICATION_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }else{
            if($verification->code == $input['code']){
                if ($verification->created_at->diffInMinutes()>30) {
                    return response()->json([
                        'message' => 'verification timeout',
                        'error_code' => ErrorCodeUtility::VERIFICATION_TIMEOUT,
                        'status_code' => '403'
                    ], 403);
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

    public function resetPassword() {
        // validation
        $input = $this->request->all();
        if($this->request->has('email')){
    	    $input['email'] = strtolower($input['email']);
        }
        
        $validator = Validator::make($input, [
            'email' => 'required_without:phone|max:50|email',
            'phone' => 'required_without:email|max:20|regex:/^\([0-9]+\)[0-9]+$/',
            'user_name' => 'required_with:phone|regex:/^[a-zA-Z0-9]*[_\-.]?[a-zA-Z0-9]*$/|min:3|max:20',
            'password' => 'required|between:8,16',
            'code' => 'required|string|max:6'
        ]);
        
        if($validator->fails()) {
    		throw new StoreResourceFailedException('Could not reset.',$validator->errors());
    	}
        
        $input_key = '';
        $input_value = '';
        $user = null;
        if($this->request->has('email')){
            $input_key = 'email';
            $input_value = $input['email'];
            $user = Users::where($input_key, '=', $input_value)->first();
        }else{
            $input_key = 'phone';
            $input_value = $input['phone'];
            $user = Users::where($input_key, '=', $input_value)->where('user_name', '=', $input['user_name'])->first();
        }

        // compare user_id and code with data in verification table
        $verification = Verifications::where($input_key,'=', $input_value)->where('type','=', 'resetpassword')->first();
        if( is_null($verification) ){
            return response()->json([
                    'message' => 'verification not found',
                    'error_code' => ErrorCodeUtility::VERIFICATION_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }else{
            if($verification->code == $input['code']){
                if ($verification->created_at->diffInMinutes()>30) {
                    return response()->json([
                        'message' => 'verification timeout',
                        'error_code' => ErrorCodeUtility::VERIFICATION_TIMEOUT,
                        'status_code' => '403'
                    ], 403);
                }
                
                if( is_null($user) ){
                    return response()->json([
                            'message' => 'user not found',
                            'error_code' => ErrorCodeUtility::USER_NOT_FOUND,
                            'status_code' => '404'
                        ], 404);
                }
                $user->password = bcrypt($input['password']);
                $user->login_count = 0;
                $user->save();
                
                $verification->delete();

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
}

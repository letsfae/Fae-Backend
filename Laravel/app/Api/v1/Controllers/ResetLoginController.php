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
            'email' => 'required|max:50|email'
        ]);
        
        if($validator->fails())
    	{
    		throw new StoreResourceFailedException('Could not verify.',$validator->errors());
    	}
        
        $user = Users::where('email','=',$input['email'])->get();
        if( is_null($user) || ( count($user)==0 ) ){
            return response()->json([
                    'message' => 'user not found',
                    'error_code' => ErrorCodeUtility::USER_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        
        // generate code & store user_id and code to verification table
        $verification_code = (string)rand(111111,999999);
        $verification = Verifications::where('email','=', $input['email'])->where('type','=', 'resetpassword')->get();
        if( is_null($verification) || ( count($verification)==0 ) ){
            $new_verification = new Verifications;
            $new_verification->type = 'resetpassword';
            $new_verification->email = $input['email'];
            $new_verification->code = $verification_code;
            $new_verification->save();
        }else{
            if ($verification[0]->created_at->diffInMinutes()>30) {
                $verification[0]->delete();
                
                $new_verification = new Verifications;
                $new_verification->type = 'resetpassword';
                $new_verification->email = $input['email'];
                $new_verification->code = $verification_code;
                $new_verification->save();
            }else{
                $verification_code = $verification[0]->code;
            }
        }
        
        // send code to this email
        $email = "Hello!\n\n";
        $email = $email."This is Fae Support. Below is the code for resetting your password. Please enter the code on your device and proceed to creating your new password. The code will be valid for 3 hours and when a new one is issued.\n";
        $email = $email."\n".$verification_code."\n\n";
        $email = $email."Have a wonderful day!\n";
        $email = $email."Fae Support\n\n";
        $email = $email."*If you did not request this change then please ignore the email.\n";
        
        Mail::raw($email, function ($message) {
            $message->from('support@letsfae.com', 'Fae Support');

            $message->to($this->request->email)->subject('Fae-Reset your password');
        });

        return $this->response->created();
        
    }

    public function verifyResetCode() {
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
    		throw new StoreResourceFailedException('Could not verify.',$validator->errors());
    	}
        
        // compare user_id and code with data in verification table
        $verification = Verifications::where('email','=', $input['email'])->where('type','=', 'resetpassword')->get();
        if( is_null($verification) || ( count($verification)==0 ) ){
            return response()->json([
                    'message' => 'verification not found',
                    'error_code' => ErrorCodeUtility::VERIFICATION_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }else{
            if($verification[0]->code == $input['code']){
                if ($verification[0]->created_at->diffInMinutes()>30) {
                    return response()->json([
                        'message' => 'verification timeout',
                        'error_code' => ErrorCodeUtility::VERIFICATION_TIMEOUT,
                        'status_code' => '403'
                    ], 403);
                }
                return $this->response->created();
                //$verification[0]->delete();
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
            'email' => 'required|max:50|email',
            'password' => 'required|between:8,16',
            'code' => 'required|string|max:6'
        ]);
        
        if($validator->fails())
    	{
    		throw new StoreResourceFailedException('Could not reset.',$validator->errors());
    	}
        
        // compare user_id and code with data in verification table
        $verification = Verifications::where('email','=', $input['email'])->where('type','=', 'resetpassword')->get();
        if( is_null($verification) || ( count($verification)==0 ) ){
            return response()->json([
                    'message' => 'verification not found',
                    'error_code' => ErrorCodeUtility::VERIFICATION_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }else{
            if($verification[0]->code == $input['code']){
                if ($verification[0]->created_at->diffInMinutes()>30) {
                    return response()->json([
                        'message' => 'verification timeout',
                        'error_code' => ErrorCodeUtility::VERIFICATION_TIMEOUT,
                        'status_code' => '403'
                    ], 403);
                }
                $verification[0]->delete();
                
                $user = Users::where('email','=',$input['email'])->get();
                $user[0]->password = bcrypt($input['password']);
                $user[0]->login_count = 0;
                $user[0]->save();
                
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

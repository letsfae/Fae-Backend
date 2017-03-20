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
use App\Api\v1\Utilities\ErrorCodeUtility;

class UserProfileController extends Controller
{
    use Helpers;

    public function __construct(Request $request)
    {
    	$this->request = $request;
    }

    public function getProfile($user_id) 
    {
        if(!is_numeric($user_id))
        {
            return response()->json([
                    'message' => 'id is not integer',
                    'error_code' => ErrorCodeUtility::INPUT_ID_NOT_NUMERIC,
                    'status_code' => '400'
                ], 400);
        }
        $user = Users::find($user_id);
        if(is_null($user))
        {
            return response()->json([
                    'message' => 'user not found',
                    'error_code' => ErrorCodeUtility::USER_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        $user_exts = User_exts::find($user_id);
        if(is_null($user_exts)) 
        {
            return response()->json([
                    'message' => 'user not found',
                    'error_code' => ErrorCodeUtility::USER_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        $profile = array('user_id' => $user->id, 'mini_avatar' => $user->mini_avatar, 'last_login_at' => $user->last_login_at);
        if($user_exts->show_user_name)
        {
            $profile['user_name'] = $user->user_name;
        }
        if($user_exts->show_birthday)
        {
            $profile['birthday'] = $user->birthday;
        }
        if($user_exts->show_email)
        {
            $profile['email'] = $user->email;
        }
        if($user_exts->show_phone)
        {
            $profile['phone'] = $user->phone;
        }
        if($user_exts->show_gender)
        {
            $profile['gender'] = $user->gender;
        }

        return $this->response->array($profile);
    }

    public function getSelfProfile() 
    {
        $user = Users::find($this->request->self_user_id);
        if(is_null($user))
        {
            return response()->json([
                    'message' => 'user not found',
                    'error_code' => ErrorCodeUtility::USER_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        $profile = array('user_id' => $user->id, 'user_name' => $user->user_name, 'mini_avatar' => $user->mini_avatar, 
                         'birthday' => $user->birthday, 'email' => $user->email, 'phone' => $user->phone, 
                         'gender' => $user->gender, 'last_login_at' => $user->last_login_at);
        return $this->response->array($profile);
    }

    public function updateSelfProfile() 
    {
        $this->updateProfileValidation($this->request);
        $user = Users::find($this->request->self_user_id);
        if(is_null($user)) 
        {
            return response()->json([
                    'message' => 'user not found',
                    'error_code' => ErrorCodeUtility::USER_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
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
        if($this->request->has('address'))
        {
            $user->address = $this->request->address;
        }
        $user->save();
        return $this->response->created();
    }

    public function getPrivacy()
    {
        $user_exts = User_exts::find($this->request->self_user_id);
        if(is_null($user_exts)) 
        {
            return response()->json([
                    'message' => 'user not found',
                    'error_code' => ErrorCodeUtility::USER_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        return $this->response->array(array(
            'show_user_name' => $user_exts->show_user_name, 
            'show_email' => $user_exts->show_email,
            'show_phone' => $user_exts->show_phone, 
            'show_birthday' => $user_exts->show_birthday, 
            'show_gender' => $user_exts->show_gender));
    }

    public function updatePrivacy() 
    {
        $this->updatePrivacyValidation($this->request);
        $user_exts = User_exts::find($this->request->self_user_id);
        if(is_null($user_exts)) 
        {
            return response()->json([
                    'message' => 'user not found',
                    'error_code' => ErrorCodeUtility::USER_NOT_FOUND,
                    'status_code' => '404'
                ], 404);
        }
        if($this->request->has('show_user_name'))
        {
            $user_exts->show_user_name = $this->request->show_user_name;
        }
        if($this->request->has('show_email'))
        {
            $user_exts->show_email = $this->request->show_email;
        }
        if($this->request->has('show_phone'))
        {
            $user_exts->show_phone = $this->request->show_phone;
        }
        if($this->request->has('show_birthday'))
        {
            $user_exts->show_birthday = $this->request->show_birthday;
        }
        if($this->request->has('show_gender'))
        {
            $user_exts->show_gender = $this->request->show_gender;
        }
        $user_exts->save();
    }

    private function updateProfileValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'filled|required_without_all:last_name,gender,birthday|string|max:50',
            'last_name' => 'filled|required_without_all:first_name,gender,birthday|string|max:50',
            'gender' => 'filled|required_without_all:first_name,last_name,birthday|string|in:male,female',
            'birthday' => 'filled|required_without_all:first_name,last_name,gender|date_format:Y-m-d|before:tomorrow|after:1900-00-00',
        ]);
        if($validator->fails())
        {
            throw new UpdateResourceFailedException('Could not update user profile.',$validator->errors());
        }
    }

    private function updatePrivacyValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'show_user_name' => 'filled|required_without_all:show_email,show_phone,show_birthday,show_gender|string|in:true,false',
            'show_email' => 'filled|required_without_all:show_user_name,show_phone,show_birthday,show_gender|string|in:true,false',
            'show_phone' => 'filled|required_without_all:show_user_name,show_email,show_birthday,show_gender|string|in:true,false',
            'show_birthday' => 'filled|required_without_all:show_user_name,show_email,show_phone,show_gender|string|in:true,false',
            'show_gender' => 'filled|required_without_all:show_user_name,show_email,show_phone,show_birthday|string|in:true,false'
        ]);
        if($validator->fails())
        {
            throw new UpdateResourceFailedException('Could not update user profile.',$validator->errors());
        }
    }
}

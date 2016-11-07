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
            return $this->response->errorBadRequest();
        }
        $user = Users::find($user_id);
        if(! is_null($user))
        {
            $profile = array('user_id' => $user->id, 'user_name' => $user->user_name, 'mini_avatar' => $user->mini_avatar);
            return $this->response->array($profile);
        }
        return $this->response->errorNotFound();
    }

    public function getSelfProfile() 
    {
        $user = Users::find($this->request->self_user_id);
        if(! is_null($user))
        {
            $profile = array('user_id' => $user->id, 'user_name' => $user->user_name, 'mini_avatar' => $user->mini_avatar);
            return $this->response->array($profile);
        }
        return $this->response->errorNotFound();
    }

    public function updateSelfProfile() 
    {
        UserProfileController::updateProfileValidation($this->request);
        UserProfileController::updateProfileUpdate($this->request);
        return $this->response->created();
    }

    private function updateProfileUpdate(Request $request)
    {
        if(count($request->all()) == 0)
        {
            return $this->response->errorBadRequest();
        }
        $user = Users::find($request->self_user_id);
        if($request->has('first_name'))
        {
            $user->first_name = $request->first_name;
        }
        if($request->has('last_name'))
        {
            $user->last_name = $request->last_name;
        }
        if($request->has('gender'))
        {
            $user->gender = $request->gender;
        }
        if($request->has('birthday'))
        {
            $user->birthday = $request->birthday;
        }
        if($request->has('address'))
        {
            $user->address = $request->address;
        }
        $user->save();
    }

    private function updateProfileValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'string|max:50',
            'last_name' => 'string|max:50',
            'gender' => 'in:male,female',
            'birthday' => 'date_format:Y-m-d|before:tomorrow|after:1900-00-00',
        ]);
        if($validator->fails())
        {
            throw new UpdateResourceFailedException('Could not update user profile.',$validator->errors());
        }
    }
}

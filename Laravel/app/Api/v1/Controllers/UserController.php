<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Validator;
use App\Users;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Exception\UpdateResourceFailedException;
use Dingo\Api\Routing\Helpers;

// use App\Http\Requests;

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
     	UserController::signUpStore($this->request);
        return $this->response->created();
    }

    public function getProfile() 
    {
        $user = Users::find($this->request->self_user_id);
        if(! is_null($user))
        {
            $profile = array('id' => $user->id, 'email' => $user->email, 'user_name' => $user->user_name, 
                'first_name' => $user->first_name, 'last_name' => $user->last_name, 'gender' => $user->gender,
                'birthday' => $user->birthday, 'role' => $user->role, 'address' => $user->address);
            return $this->response->array($profile);
        }
        return $this->response->errorNotFound();
    }

    public function updateProfile() 
    {
        UserController::updateProfileValidation($this->request);
        UserController::updateProfileUpdate($this->request);
        return $this->response->created();
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
    		// throw new UnprocessableEntityHttpException('Could not create new user.');
    	}
    }

    private function signUpStore(Request $request) 
    {
		$user = new Users;
		$user->email = strtolower($request->email);
		$user->password = bcrypt($request->password);
		$user->first_name = $request->first_name;
		$user->last_name = $request->last_name;
        $user->gender = $request->gender;
		$user->birthday = $request->birthday;
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

    private function updateProfileUpdate(Request $request)
    {
        if(count($request->all()) > 1)
        {
            throw new UpdateResourceFailedException('Could not update user profile.');
        }
        $user = Users::find($request->self_user_id);
        if($request->has('first_name'))
        {
            $user->first_name = $request->first_name;
        }
        else if($request->has('last_name'))
        {
            $user->last_name = $request->last_name;
        }
        else if($request->has('gender'))
        {
            $user->gender = $request->gender;
        }
        else if($request->has('birthday'))
        {
            $user->birthday = $request->birthday;
        }
        $user->save();
    }
}

<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Validator;
use App\Users;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Dingo\Api\Exception\StoreResourceFailedException;
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
     	return UserController::store($this->request);
    }

    public function validation(Request $request)
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

    public function store(Request $request) 
    {
    	UserController::validation($request);

		$user = new Users;
		$user->email = strtolower($request->email);
		$user->password = bcrypt($request->password);
		$user->first_name = $request->first_name;
		$user->last_name = $request->last_name;
		$user->birthday = $request->birthday;
		$user->save();
        return $this->response->created();
    }

    public function getProfile() {
    	echo 'In get profile';
    }
}

<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;
use Auth;
use App\Users;
use Validator;

class ExistenceController extends Controller {
    use Helpers;
    
    public function __construct(Request $request) {
    	$this->request = $request;
    }

    public function email($email) {	
		if ((filter_var($email, FILTER_VALIDATE_EMAIL) == false) || empty($email)) {
    		throw new AccessDeniedHttpException('Bad request, Please check your input email type!'); 
		}
		$user = Users::where('email', '=', $email)->first();
		if ($user == null) {
			throw new AccessDeniedHttpException('Bad request, No such email exists!');
		}
		return $this->response->array(null);            
    }

    public function userName($user_name) {
    	if (empty($user_name)) {
    		throw new AccessDeniedHttpException('Bad request, Please check your input user_name!'); 
    	}
    	$user = Users::where('user_name', '=', $user_name)->first();
    	if ($user == null) {
			throw new AccessDeniedHttpException('Bad request, No such user_name exists!');
		}
		return $this->response->array(null); 

    }
}
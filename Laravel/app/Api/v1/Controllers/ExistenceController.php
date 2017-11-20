<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Dingo\Api\Exception\UpdateResourceFailedException;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;
use App\Api\v1\Controllers\UserController;
use App\Name_cards;
use Auth;
use App\Users;
use Validator;

class ExistenceController extends Controller {
    use Helpers;
    
    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function email($email) {
        $email = strtolower($email);
        $input = array('email' => $email);
        $validator = Validator::make($input, [
        'email' => 'required|max:50|email',
        ]);
        if($validator->fails())
        {            
            throw new AccessDeniedHttpException('Bad request, Please verify your email format!');
        }
        $user = Users::where('email', '=', $email)->first();
        $existence = is_null($user) ? false : true;
        $user_id = is_null($user) ? null : $user->id;
        $user_name = is_null($user) ? null : $user->user_name;
        $nick_name = is_null($user) ? null : Name_cards::find($user->id)->nick_name;
        $result = array('existence' => $existence, 'user_id' => $user_id, 'user_name' => $user_name, 'nick_name' => $nick_name);
        return $this->response->array($result);
    }

    public function userName($user_name) {
        $input = array('user_name' => $user_name);
        $validator = Validator::make($input, [
            'user_name' => 'required|regex:/^[a-zA-Z0-9]*[_\-.]?[a-zA-Z0-9]*$/|min:3|max:20',
        ]);
        if($validator->fails())
        {            
            throw new AccessDeniedHttpException('Bad request, Please verify your user_name format!');
        }
        $user = Users::whereRaw('LOWER(user_name) = ?', [strtolower($this->request->user_name)])->first();
        $existence = is_null($user) ? false : true;
        $user_id = is_null($user) ? null : $user->id;
        $user_name = is_null($user) ? null : $user->user_name;
        $nick_name = is_null($user) ? null : Name_cards::find($user->id)->nick_name;
        $result = array('existence' => $existence, 'user_id' => $user_id, 'user_name' => $user_name, 'nick_name' => $nick_name);
        return $this->response->array($result);
    }

    public function getUserByPhoneBatched() {
        $validator = Validator::make($this->request->all(), [
            'phone' => 'required|string|regex:/^(\([0-9]+\)[0-9]+\;)*\([0-9]+\)[0-9]+$/'
        ]);
        if($validator->fails())
        {
            throw new UpdateResourceFailedException('Could not update user location.',$validator->errors());
        }
        $phones = explode(';', $this->request->phone);
        $result = array();
        foreach($phones as $phone)
        {
            $user = Users::where('phone', $phone)->first();
            if(!is_null($user)) {
                $result[$phone] = array('user_id' => $user->id, 
                    'relation' => UserController::getRelationBetween($this->request->self_user_id, $user->id));
            }
        }
        return $this->response->array($result);
    }
}
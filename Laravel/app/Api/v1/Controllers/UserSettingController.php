<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Validator;
use App\Users;
use App\UserSettings;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Dingo\Api\Exception\UpdateResourceFailedException;
use Dingo\Api\Routing\Helpers;
use App\Api\v1\Utilities\ErrorCodeUtility;

class UserSettingController extends Controller
{
    use Helpers;

    public function __construct(Request $request)
    {
    	$this->request = $request;
    }

    public function get() {
    	$user_setting = UserSettings::find($this->request->self_user_id);
    	return $this->response->array($user_setting->toArray());
    }

    public function update() {
    	$this->updateValidation($this->request);
    	$user_setting = UserSettings::find($this->request->self_user_id);
    	if($this->request->has('email_subscription')) {
    		$user_setting->email_subscription = $this->request->email_subscription;
    	}
    	if($this->request->has('show_name_card_options')) {
    		$user_setting->show_name_card_options = $this->request->show_name_card_options;
    	}
    	if($this->request->has('measurement_units')) {
    		$user_setting->measurement_units = $this->request->measurement_units;
    	}
    	if($this->request->has('shadow_location_system_effect')) {
    		$user_setting->shadow_location_system_effect = $this->request->shadow_location_system_effect;
    	}
        if($this->request->has('others')) {
            $user_setting->others = $this->request->others;
        }
    	$user_setting->save();
    	return $this->response->created();
    }

    private function updateValidation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email_subscription' => 'filled|required_without_all:show_name_card_options,measurement_units,shadow_location_system_effect,others|in:true,false',
            'show_name_card_options' => 'filled|required_without_all:email_subscription,measurement_units,shadow_location_system_effect,others|in:true,false',
            'measurement_units' => 'filled|required_without_all:email_subscription,show_name_card_options,shadow_location_system_effect,others|in:imperial,metric',
            'shadow_location_system_effect' => 'filled|required_without_all:email_subscription,show_name_card_options,measurement_units,others|in:min,normal,max',
            'others' => 'filled|required_without_all:email_subscription,show_name_card_options,measurement_units,shadow_location_system_effect',
        ]);
        if($validator->fails())
        {
            throw new UpdateResourceFailedException('Could not update user setting.',$validator->errors());
        }
    }
}

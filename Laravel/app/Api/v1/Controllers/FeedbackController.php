<?php

namespace App\Api\v1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Dingo\Api\Routing\Helpers;
use App\Api\v1\Utilities\MailUtility;
use Dingo\Api\Exception\UpdateResourceFailedException;
use App\Api\v1\Utilities\ErrorCodeUtility;
use Validator;

class FeedbackController extends Controller
{
    use Helpers;

    public function __construct(Request $request) {
    	$this->request = $request;
    }

    public function sendFeedback() 
    {
    	$this->sendFeedbackValidation($this->request);
    	$email = 'feedback@letsfae.com';
        $title = 'Fae Support';
    	switch ($this->request->type) 
        {
    		case 'feedback':
    			$email = 'feedback@letsfae.com';
    			break;
    		case 'report':
    			$email = 'support@letsfae.com';
    			break;
    		case 'tag':
    			$email = 'feedback@letsfae.com';
    			break;
    		default:
    			return response()->json([
                            'message' => 'wrong type',
                            'error_code' => ErrorCodeUtility::WRONG_TYPE,
                            'status_code' => '400'
                            ], 400);
    	}
        $content = $this->request->content;
        MailUtility::sendMail('support@letsfae.com', $email, $title, $content, $this->request->type, $this->request->self_user_id);
    }

    private function sendFeedbackValidation(Request $request)
    {
    	$validator = Validator::make($request->all(), [
            'type' => 'required|in:feedback,report,tag',
            'content' => 'required|string|max:500'
        ]);
        if($validator->fails())
        {
            throw new UpdateResourceFailedException('Could not update user account.',$validator->errors());
        }
    }
}

<?php
namespace App\Api\v1\Utilities;
use App\Users;
use Mail;

class MailUtility
{
	public static function sendMail($from, $to, $title, $content, $subject, $user_id)
	{
		$user = Users::find($user_id);
		$user_info = "user_id: ".$user->id."\n";
		$user_info = $user_info."email: ".$user->email."\n";
		$user_info = $user_info."user name: ".$user->user_name."\n";
		$user_info = $user_info."last name: ".$user->last_name."\n";
		$user_info = $user_info."first_name: ".$user->first_name."\n";
		$user_info = $user_info."phone: ".$user->phone."\n";
		//$user_info = $user_info."gender: ".$user->gender."\n";
		$content = $user_info."\n\n".$content;
		Mail::raw($content, function ($message) use ($from, $to, $title, $subject)
		{
            $message->from($from, $title);
            $message->to($to)->subject($subject);
        });
	}
}
<?php
namespace App\Api\v1\Utilities;

class TimeUtility
{
	public static function timeFormat($time)
	{
		if(is_object($time))
		{
			$date = $time->timezone('UTC')->format("Y-m-d H:i:s T");
			return $date;
		}
		else if(is_string($time))
			return $time;
	}
}
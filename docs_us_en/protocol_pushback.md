# Pushback Interface

When related event happened, the server will pushback the message initially （only push to the device which `is_mobile` is true). If the client does not register the device_id, then pushback meassges can not be received and check whether the unread message exists can be made by sync interface initially (or decide current status accroding to the return error information by calling other interfaces). 

All the format of the pushback message is json. Specified pushback type will be marked by type field. 

##  authentication other device :white_check_mark:

	{
		"type": "authentication_other_device",
		"device_id": @number, other device id (can not be empty)
		"fae_client_version": @string, other device client version number 
		"auth": @boolean if this is true, then the users authenticaion is valid, or the user has been forced to logged out
	}

## friends new request

	{
		"type": "friends_new_request",
		"request_user_id": @number, request user
	}

## friends request reponse

	{
		"type": "friends_request_reponse",
		"requested_user_id": @number, requested user
		"result": @string("accept","ignore")
	}

## new message :white_check_mark:

	{
		"type": "chat_new_message",
		"chat_id": @number,
		"last_message": @string,
		"last_message_sender_id": @number,
		"last_message_timestamp": @string,
		"last_message_type": @string,
		"unread_count": @number
	}

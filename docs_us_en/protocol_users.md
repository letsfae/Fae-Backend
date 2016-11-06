# User and Authentication 

## Sign up :white_check_mark:

`POST /users`

### auth

no

### parameters

| Name | Type | Description |
| --- | --- | --- |
| password | string(8-16) | password |
| email | string(50) | email |
| user_name | string(30) | user name |
| first_name | string(50) | first name |
| last_name | string(50) | last name |
| birthday | string(YYYY-MM-DD) | birthday |
| gender | string("male", "female") | gender |

The format of user_name is: start with character and only the uppercase and lowercase letters, numbers and underscores are permitted, and the length should be 6-30.

### response

Status: 201

## log in :white_check_mark:

`POST /authentication`

### auth

no

### parameters

| Name | Type | Description |
| --- | --- | --- |
| user_name | string(30) | user name |
| email | string(50) | email |
| password | string(8-16) | password |
| device_id(optional) | string(1~150) | device id，the default value is null |
| is_mobile(optional) | boolean | whether it is mobile, the default value is false |

Only one of user_name or email need to be chosen (or relationship , the other filed is not needed). If both exist, take the email as the standard. 

The device_id is used to do the pushback notification to the server. If does not exist, it will not push notification. 

If is_mobile is true, the other acoount that has been used to log in another mobile equipment (non mobile equipment will not be influenced). 

If differnt user accounts are used to log in with the same device_id, the previous users will be forced to log out. 

When the errors are caused three times after login, the users account will be forbidden to login forever and in order to relieve the prohibition, reset_login interface need to be called.  

### response

Status: 201

	{
		"user_id": @number
		"token": @string
		"session_id": @number
	}

return login_count when error caused.

## logout :white_check_mark:

`DELETE /authentication`

### auth

yes

### response

Status: 204

## get reset_login email :white_check_mark:

`POST /reset_login/code`

### auth

no

### parameters

| Name | Type | Description |
| --- | --- | --- |
| email | string(50) | email |

The valid time for the code is 30 minuters after it is sent, and the code will not change if it is obtained again in 30 minutes.

### response

Status: 201

## verify reset_login code :white_check_mark:

`POST /reset_login/code/verify`

### auth

no

### parameters

| Name | Type | Description |
| --- | --- | --- |
| email | string(50) | email |
| code | string(6) | six digits code in the email (passing by the type of string) |

### response

Status: 201

## reset_login password after verifying :white_check_mark:

`POST /reset_login/password`

### auth

no

### parameters

| Name | Type | Description |
| --- | --- | --- |
| email | string(50) | email |
| code | string(6) | six digits code in the email (passing by the type of string) |
| password | string(8-16) | password |

### response

Status: 201

## verify whether the email exists :white_check_mark:

`GET /existence/email/:email`

### auth

no

### response

Status: 200

	{
		"existence": @boolean
	}

## verify whether the user_name exists :white_check_mark:

`GET /existence/user_name/:user_name`

### auth

no

### response

Status: 200

	{
		"existence": @boolean
	}

## get account :white_check_mark:

`GET /users/account`

### auth

yes

### response

Status: 200

	{
		"email": @string,
		"email_verified": @boolean,
		"user_name": @string,
		"first_name": @string,
		"last_name": @string,
		"gender": @string,
		"birthday": @string,
		"phone": @string(xxx-xxx-xxxx),
		"phone_verified": @boolean,
		"mini_avatar": @number the mini_avatar showed in the map. the default value is 0 if it is not set
	}

## update account :white_check_mark:

`POST /users/account`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| first_name | string(50) | first_name |
| last_name | string(50) | last_name |
| birthday | string(YYYY-MM-DD) | birthday |
| gender | string("male", "female") | gender |
| user_name | string(30) | user_name（the interface might be called individually and set) |
| mini_avatar | integer | the mini_avatar showed in the map |

All the fileds are optional, but at least one field is included. All these interfaces have no special operation (If it has special operation, specific interface is needed, such as updating password). 

Please pay attention that the format of the user_name is: start with character and only the uppercase and lowercase letters, numbers and underscores are permitted, and the length should be 6-30.

### response

Status: 201

## verify password :white_check_mark:

`POST /users/account/password/verify`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| password | string(8-16) | password|
 
When the errors are caused three times after login, the users account will be locked and will log out (Auth void automatically). The reset_login interface need to be called when try to unlock.  

### response

Status: 201

return login_count when error caused.

## update password :white_check_mark:

`POST /users/account/password`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| old_password | string(8-16) | old password |
| new_password | string(8-16) | new password |

### response

Status: 201

return login_count when error caused.

## update email :white_check_mark:

`POST /users/account/email`
 
The verification code will be received in the new email after updating the email, and verfy email interface needed to be called in order to finish the email verification。 The valid time for the code is 30 minuters after it is sent.

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| email | string(50) | new email |

### response

Status: 201

## verify email :white_check_mark:

`POST /users/account/email/verify`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| email | string(50) | new email |
| code | string(6) | six digits code (passing by the type of string) |

### response

Status: 201

## update phone :white_check_mark:

`POST /users/account/phone`
 
The verification code will be received in the new phone after updating the phone number, and verfy phone interface needed to be called in order to finish the phone verification。 The valid time for the code is 30 minuters after it is sent.

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| phone | string(xxx-xxx-xxxx) | new phone number |

### response

Status: 201

## verify phone :white_check_mark:

`POST /users/account/phone/verify`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| phone | string(xxx-xxx-xxxx) | new phone number |
| code | string(6) | six digits code (passing by the type of string) |

### response

Status: 201

## get self profile (pending)

`GET /users/profile`

### auth

yes

### response

Status: 200

	{
		"xxx": @xxx
	}

## get profile of other users (pending)

`GET /users/:user_id/profile`

Others are the same as get self profile。

Please pay attention: only the public field that the user set is obtained. 

## update self profile (pending)

`POST /users/profile`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| xxx | number | xxx |

All the field are optional, but at least one field is included. 

### response

Status: 201

## get self profile privacy (pending)

`GET /users/profile/privacy`

### auth

yes

### response

Status: 200

	{
		"xxx": @xxx
	}

## update self profile privacy (pending)

`POST /users/profile/privacy`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| xxx | boolean | xxx |

All the field are optional, but at least one field is included. 

### response

Status: 201

## get self status :white_check_mark:

`GET /users/status`

### auth

yes

### response

Status: 200

	{
		"status": @number 0 to 5 represent offline/online/no distrub/busy/away/invisible,
		"message": @string
	}


The user status is shared in different equipments. 

The user status is not reserved by the server: that is when the user logged in using the first equipment, its status is set to online. When the last equipment logged out, its status is set to offline.  

## get status of other users :white_check_mark:

`GET /users/:user_id/status`

Almost the same as get self status。

Please pay attention: when obtaining the status of other users (except for self user_id), the invisible status can not be obtained (even if the status of the user is invisible, the return status is also offline). 

## update self status :white_check_mark:

`POST /users/status`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| status | number | 0:offline, 1:online, 2:no distrub, 3:busy, 4:away, 5:invisible |
| message | string(100) | short status (can be empty) |

All the field are optional, but at least one field is included. 

### response

Status: 201

## get the NameCard of a specific user :white_check_mark:

`GET /users/:user_id/name_card`

### auth

yes

### response

Status: 200

	{
		"nick_name": @string,
		"short_intro": @string,
		"tags": [
			{
				"tag_id": @number,
				"title": @string,
				"color": @string
			},
			{...},
			{...}
		],
		"gender": @string same as the set in account 
	}

## get self NameCard :white_check_mark:

`GET /users/name_card`

Others are the same as getting the NameCard of a specific user. 

## get the tags that all the NameCard belonged to :white_check_mark:

`GET /users/name_card/tags`

This interface is used to get the tag of all the system built-in namecards. 

### auth

yes

### response

Status: 200

	[
		{
			"tag_id": @number,
			"title": @string,
			"color": @string
		},
		{...},
		{...}
	]

## update NameCard :white_check_mark:

`POST /users/name_card`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| nick_name | string(50) | nick name |
| short_intro | string(200) | short introduction (can be empty) |
| tag_ids | number | all the ids of the tag, seperated by semicolon, and at most three tags |

At least one field above need to be included. 

### response

Status: 201

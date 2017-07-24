# Other Interface

## sync message :white_check_mark:

`GET /sync`

Used to get the amount of the sync messages (whether has new sync message) and also used to decide wether the conncetion has been made (such as wether the user is in the login status when enter the app again).

### auth

yes

### response

Status: 200

	{
		"friend_request": @number, friend request amount
		"chat": @number, unread message amount
		"chat_room": @number unread chat room message amount
	}

## feedback :white_check_mark:

`POST /feedback`

This interface is used to get the user feedback (the back end will relevant users contacts, such as email and cellphone).

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| type | string('feedback','report','tag') | feedback type |
| content | string(500) | feedback content |

Currently, the report will be sent to support@letsfae.com and feedback / tag will be sent to feedback@letsfae.com.

### response

Status: 201

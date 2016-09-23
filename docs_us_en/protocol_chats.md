# Friends and Chats Interface 


## post friends request 

`POST /friends/request`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| requested_user_id | number | requested user id |

### response

Status: 201

## accept friend request

`POST /friends/accept`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| friend_request_id | number | friend reqeust id |

### response

Status: 201

## ignore friend request 

`POST /friends/ignore`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| friend_request_id | number | friend request id |

### response

Status: 201

## delete friend 

`DELETE /friends/:user_id`

### auth

yes

### response

Status: 204

## get all the friend requests

`GET /friends/request`

If the amount of friend_request that we get from the sync interface is not 0, we can call this interface to get all the friend requests.  

### auth

yes

### response

Status: 200

	[
		{
			"friend_request_id": @number,
			"request_user_id": @number,
			"request_user_name": @string,
			"request_email": @string,
			"created_at": @string
		},
		...		
	]

the user_name and email are superfluous in order to show easily. 

## get the friend list

...


----------

The process of calling the chats interface: sync->get unread(get unread array)->[get one from firebase]->mark read (only to that message that was got from the firebase)->send/go back to unread array (deal with the left messages), get history is used to create the initial list. 

----------

## send chat message :white_check_mark:

`POST /chats`

While sending the chat messages to the firebase, a piece of message should be also sent to the server. 

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| receiver_id | number | receiver user id |
| message | string | message content |
| type | string('text','image') | distinct the content type  |

### response

Status: 201

	{
		"chat_id": @number
	}

The chat_id is the chat room id for the two chat objects. To the server, A chatting with B and B chatting with A is in the same chat room. 

## get unread message :white_check_mark:

`GET /chats/unread`

### auth

yes

### response

Status: 200

	[
		{
			"chat_id": @number,
			"last_message": @string,
			"last_message_sender_id": @number,
			"last_message_timestamp": @string,
			"last_message_type": @string,
			"unread_count": @number
		},
		{...},
		{...}
	]

The amount of unread messages can be got from the unread_count, and then self-destruct can be implemented (get the latest n messages according to the unread_count). 

## mark read :white_check_mark:

`POST /chats/read`

This interface is used to mark that the message has been read, then the amount of the unread messages will be set to 0 in the user's chat after calling the interface.  

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| chat_id | number | chat id |

### response

Status: 201

## get chat history :white_check_mark:

`GET /chats`

The interface is usually called when initializing the chat list. 

### auth

yes

### response

Status: 200

	[
		{
			"chat_id": @number,
			"with_user_id": @number chat with the user of the user id,
			"last_message": @string,
			"last_message_sender_id": @number,
			"last_message_type": @string,
			"last_message_timestamp": @string,
			"unread_count": @number
		},
		{...},
		{...}
	]

## delete chat :white_check_mark:

`DELETE /chats/:chat_id`

This interface is used to delete the chat. After any side of the chatting objects doing the delete operation, the chat messages will be deleted forever and the unread messages will also not be saved any more. 

### auth

yes

### response

Status: 204


----------

The chat could only be done in the created ChatRoom. 

----------

## send message in the ChatRoom 

`POST /chat_rooms/:chat_room_id/message`

As long as the message is sent by the user, this user will be the participant of the ChatRoom. 

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| message | string | specific content |
| type | string('text','image') | distinguish the type of the content |

### response

Status: 201

## get all the ChatRooms including the unread messages

`GET /chat_rooms/unread`

### auth

yes

### response

Status: 200

	[
		{
			"chat_room_id": @number,
			"title": @string,
			"user_id": @number creator id
			"geolocation": {
				"latitude": @number,
				"longitude": @number
			},
			"last_message": @string,
			"last_message_sender_id": @number,
			"last_message_type": @string,
			"last_message_timestamp": @string,
			"unread_count": @number
			"created_at": @string
		},
		{...},
		{...}
	]


## mark ChatRooms with all the read messages

`POST /chat_rooms/:chat_room_id/read`

This interface is used to mark the message is read, the unread message of the user in the ChatRoom will be set to 0 after calling. 

### auth

yes

### response

Status: 201

## get all the ChatRooms that the user participated in (not "create") 

`GET /chat_rooms`

Usually called when initializing the list of the chat. 

### auth

yes

### response

Status: 200

	[
		{
			"chat_room_id": @number,
			"title": @string,
			"user_id": @number 创建者id
			"geolocation": {
				"latitude": @number,
				"longitude": @number
			},
			"last_message": @string,
			"last_message_sender_id": @number,
			"last_message_type": @string,
			"last_message_timestamp": @string,
			"unread_count": @number
			"created_at": @string
		},
		{...},
		{...}
	]

## get all the users in the ChatRoom. 

`GET /chat_rooms/:chat_room_id/users`

### auth

yes

### response

Status: 200

	[
		{
			"chat_room_id": @number,
			"user_id": @number,
			"created_at": @string
		},
		{...},
		{...}
	]

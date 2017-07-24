# Chats Interface 

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
| type | string('text','image','sticker','location','audio','customize') | distinct the content type  |

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
			"last_message_sender_name": @string,
			"last_message_timestamp": @string,
			"last_message_type": @string,
			"unread_count": @number,
			"server_sent_timestamp":@string
		},
		{...},
		{...}
	]

Sort the result in descending order according to the last_message_timestamp
The amount of unread messages can be got from the unread_count, and then self-destruct can be implemented (get the latest n messages according to the unread_count). 

## get messages of the two users (according to the chat_id)

`GET /chats/:chat_id`

### filters

| Name | Type | Description |
| --- | --- | --- |
| count | number | get this amount of chat messages |
| offset | number | get chat message from the specific one，the base is 1 |

The chat messages are returned according to the timestamp in descending order.

### auth

yes

### response

Status: 200

	[
		{
			"chat_id": @number,
			"message": @string,
			"message_sender_id": @number,
			"message_sender_name": @string,
			"message_timestamp": @string,
			"message_type": @string,
			"server_sent_timestamp":@string
		},
		{...},
		{...}
	]

## get messages of the two users (according to the user_id)

`GET /chats/:user_a_id/:user_b_id`

The order of the a and b is not sensitive. 

Others are the same as the interface of `get messages of the two users (according to the chat_id)`.

This interface is looked as the merge operation of the interfaces of `get chat_id according to the two users' user_id` and `get messages of the two users (according to the chat_id)`.

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
			"with_user_id": @number 与该id用户聊天,
			"with_user_name": @string,
			"with_nick_name": @string,
			"last_message": @string,
			"last_message_sender_id": @number,
			"last_message_sender_name": @string,
			"last_message_type": @string,
			"last_message_timestamp": @string,
			"unread_count": @number,
			"server_sent_timestamp":@string
		},
		{...},
		{...}
	]

The result is sorted in the descending order of the last_message_timestamp.

## delete chat :white_check_mark:

`DELETE /chats/:chat_id`

This interface is used to delete the chat room. After any side of the chatting objects doing the delete operation, the chat messages will be deleted forever and the unread messages will also not be saved any more. 

### auth

yes

### response

Status: 204

## get chat_id according to the two users' user_id :white_check_mark:

`GET /chats/users/:user_a_id/:user_b_id`

The order of the a and b is not sensitive.

### auth

yes

### response

Status: 200

	{
		"chat_id": @number
	}

----------

The chat could only be done in the created ChatRoom. 

----------

## send message in the ChatRoom :white_check_mark:

`POST /chat_rooms/:chat_room_id/message`

As long as the message is sent by the user, this user will be the participant of the ChatRoom. 

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| message | string | specific content |
| type | string('text','image','sticker','location','audio') | distinguish the type of the content |

### response

Status: 201

## get all the ChatRooms including the unread messages :white_check_mark:

`GET /chat_rooms/message/unread`

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
			"last_message_sender_name": @string,
			"last_message_type": @string,
			"last_message_timestamp": @string,
			"unread_count": @number
			"created_at": @string,
			"server_sent_timestamp":@string
		},
		{...},
		{...}
	]

The result is sorted in the descending order of the last_message_timestamp.

## mark chatrooms with all the is_read messages :white_check_mark:

`POST /chat_rooms/:chat_room_id/read`

This interface is used to mark the message is read, the unread message of the user in the ChatRoom will be set to 0 after calling. 

### auth

yes

### response

Status: 201

## get all the ChatRooms that the user participated in (not "create") :white_check_mark:

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
			"user_id": @number, creator id
			"geolocation": {
				"latitude": @number,
				"longitude": @number
			},
			"last_message": @string,
			"last_message_sender_id": @number,
			"last_message_sender_name": @string,
			"last_message_type": @string,
			"last_message_timestamp": @string,
			"unread_count": @number
			"created_at": @string,
			"server_sent_timestamp": @string
		},
		{...},
		{...}
	]

The result is sorted in the descending order of the last_message_timestamp.

## get all the users in the ChatRoom :white_check_mark:

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

# Chats Interface (New)

In the new chats interface, as long as the unread messages are got, they will be deleted from the back end, so the front end should be responsible for the store of he messages. The mark read interface is integrated and the get history interface is removed. 

attention: Considering the compatible problems that are caused during the moving of the interfaces of the front end and the back end, postfix `_v2` is added to the new interface temporarily. After the testing of the interface, all the old chats interfaces will be deleted and postfix `_v2` will be deleted too. 

----------

The process of calling the chats interface: from sync get the unread message->from get unread get unread array->[get unread message from a chat_id]->send / go back to unread array to deal with the rest of the messages.

----------
 
## send chat message

`POST /chats_v2`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| receiver_id | number | receiver id |
| message | string | message content |
| type | string('text','image','sticker','location','audio','customize') | distinguish the type of the content |

The message content of the type of customize can be defined by itself.

### response

Status: 201

	{
		"chat_id": @number
	}

The chat_id is the chat room id for the two chat objects. To the server, A chatting with B and B chatting with A is in the same chat room. 

## get unread message

`GET /chats_v2/unread`

### auth

yes

### response

Status: 200

	[
		{
			"chat_id": @number,
			"last_message": @string,
			"last_message_sender_id": @number,
			"last_message_sender_name": @string,
			"last_message_timestamp": @string,
			"last_message_type": @string,
			"unread_count": @number,
			"server_sent_timestamp":@string
		},
		{...},
		{...}
	]

The result is sorted in the descending order of the last_message_timestamp.

## get messages of the two users (according to the chat_id)

`GET /chats_v2/:chat_id`

This interface is used to return all the messages between the two chat users.

Each time calling the interface will get at most 50 picies of data (only used to prevent the large flow for each request). If the unread_count in the response header is not 0 after the request, continuous request need to be made in order to get all the unread data. 

Sort the return data in the ascending order (the old message will arrrive at the earliest time).

### auth

yes

### response

Status: 200
	
	unread_count: @number, The amount of the unread messages still left after the calling of this interface

	-----

	[
		{
			"chat_id": @number,
			"message": @string,
			"message_sender_id": @number,
			"message_sender_name": @string,
			"message_timestamp": @string,
			"message_type": @string,
			"server_sent_timestamp":@string
		},
		{...},
		{...}
	]

## get messages of the two users (according to the user_id)）

`GET /chats_v2/:user_a_id/:user_b_id`

The order of the a and b is not sensitive. 

Others are the same as the interface of `get messages of the two users (according to the chat_id)`.

This interface is looked as the merge operation of the interfaces of `get chat_id according to the two users' user_id` and `get messages of the two users (according to the chat_id)`.

## delete chat

`DELETE /chats_v2/:chat_id`

This interface is used to delete the chat room. After any side of the chatting objects doing the delete operation, the chat messages will be deleted forever and the unread messages will also not be saved any more.

### auth

yes

### response

Status: 204

## get chat_id according to the two users

`GET /chats_v2/users/:user_a_id/:user_b_id`
 
The order of the a and b is not sensitive. 

### auth

yes

### response

Status: 200

	{
		"chat_id": @number
	}

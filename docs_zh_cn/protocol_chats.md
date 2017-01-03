# 聊天类接口

----------

聊天类接口调用流程：sync->get unread获取到unread array->[get one from firebase]->mark read（仅对从firebase读取到的那条）->send / 继续回unread array处理剩余消息，get history仅用于构筑初始化列表

----------

## 发送新聊天消息 send chat message :white_check_mark:

`POST /chats`

在发给firebase聊天信息的同时，也需要发给服务器一份聊天消息。

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| receiver_id | number | 目标用户id |
| message | string | 具体内容 |
| type | string('text','image','sticker','location','audio') | 区分内容的类型 |

### response

Status: 201

	{
		"chat_id": @number
	}

chat_id为聊天双方的聊天室id，对服务器来说，A和B聊天及B和A聊天被视为在同一个聊天室中进行。

## 获取未读消息 get unread message :white_check_mark:

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

结果按照last_message_timestamp降序排序。

可通过unread_count来获取有几条消息未读，从而实现“阅后即焚”（即通过unread_count来实现只获取最近的n条消息）。

## 标记已读消息 mark read :white_check_mark:

`POST /chats/read`

此接口用于标记消息已读，调用后则将置0该用户在该会话中的未读消息数量。

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| chat_id | number | 聊天id |

### response

Status: 201

## 获取该用户所有聊天消息 get chat history :white_check_mark:

`GET /chats`

一般在初始化聊天列表时调用。

### auth

yes

### response

Status: 200

	[
		{
			"chat_id": @number,
			"with_user_id": @number 与该id用户聊天,
			"with_user_name" @string,
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

结果按照last_message_timestamp降序排序。

## 删除聊天（室） delete chat :white_check_mark:

`DELETE /chats/:chat_id`

此接口用于删除聊天室。聊天双方中任意一方执行删除操作后，双方的聊天信息都将被永久删除，未读消息也将不被保留。

### auth

yes

### response

Status: 204

## 根据聊天双方user_id获取chat_id

`GET /chats/users/:user_a_id/:user_b_id`

a与b的顺序不敏感。

### auth

yes

### response

Status: 200

	{
		"chat_id": @number
	}


----------

在ChatRoom中聊天只能在已经创建的ChatRoom中进行。

----------

## 发送ChatRoom聊天消息 :white_check_mark:

`POST /chat_rooms/:chat_room_id/message`

一旦用户发送消息，该用户即成为该ChatRoom参与者。如果capacity达到上限，则无法再加入。

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| message | string | 具体内容 |
| type | string('text','image','sticker','location','audio') | 区分内容的类型 |

### response

Status: 201

## 获取所有含有未读消息的ChatRoom :white_check_mark:

`GET /chat_rooms/message/unread`

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

结果按照last_message_timestamp降序排序。

## 标记已读ChatRoom :white_check_mark:

`POST /chat_rooms/:chat_room_id/message/read`

此接口用于标记消息已读，调用后则将置0该用户在该ChatRoom中的未读消息数量。

### auth

yes

### response

Status: 201

## 获取用户参与（不是“创建”）的所有ChatRoom :white_check_mark:

`GET /chat_rooms`

一般在初始化聊天列表时调用。

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

结果按照last_message_timestamp降序排序。

## 获取ChatRoom中所有用户 :white_check_mark:

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

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
| type | string('text','image','sticker','location','audio','customize') | 区分内容的类型 |

其中customize类型的message内容可自定义。

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

## 获取两名用户之间的消息（通过chat_id）

`GET /chats/:chat_id`

### filters

| Name | Type | Description |
| --- | --- | --- |
| count | number | 获取该数量的聊天记录 |
| offset | number | 从第几条开始获取聊天记录，base为1 |

注意聊天记录是按照时间降序返回的。

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

## 获取两名用户之间的消息（通过user_id）

`GET /chats/:user_a_id/:user_b_id`

a与b的顺序不敏感。

其余同`通过chat_id获取`的接口。

该接口可视为`根据聊天双方user_id获取chat_id`与`获取两名用户之间的消息（通过chat_id）`接口的合并操作。

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

结果按照last_message_timestamp降序排序。

## 删除聊天（室） delete chat :white_check_mark:

`DELETE /chats/:chat_id`

此接口用于删除聊天室。聊天双方中任意一方执行删除操作后，双方的聊天信息都将被永久删除，未读消息也将不被保留。

### auth

yes

### response

Status: 204

## 根据聊天双方user_id获取chat_id :white_check_mark:

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


# 聊天接口（新）

新聊天接口中，所有的未读消息一旦被获取，将会从后端删除，因此需由前端负责消息的存储。新接口中整合了mark read接口并移除了get history接口。

注，考虑到前后端接口迁移过程中可能会存在兼容问题，暂时将新接口加入`_v2`后缀，待接口测试完毕后，所有老chat接口将被删除，且新接口的`_v2`后缀将被删除。

----------

聊天类接口调用流程：sync发现有未读消息->get unread获取到unread array->[获取某个chat_id中的未读消息]->send / 继续回unread array处理剩余消息。

----------

## 发送新聊天消息 send chat message

`POST /chats_v2`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| receiver_id | number | 目标用户id |
| message | string | 具体内容 |
| type | string('text','image','sticker','location','audio','customize') | 区分内容的类型 |

其中customize类型的message内容可自定义。

### response

Status: 201

	{
		"chat_id": @number
	}

chat_id为聊天双方的聊天室id，对服务器来说，A和B聊天及B和A聊天被视为在同一个聊天室中进行。

## 获取未读消息 get unread message

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

结果按照last_message_timestamp降序排序。

## 获取两名用户之间的消息（通过chat_id）

`GET /chats_v2/:chat_id`

该接口用于返回两个聊天用户之间的所有消息。

每次调用该接口将获取最多50条数据数据（仅用于防止单次请求流量过大），如果请求后response header中的unread_count不为0，则需要一直请求该接口获取所有未读数据。当所有未读消息均被获取后，用户在该会话中的未读消息数量将被置0。

数据返回按照时间升序排序（即老的消息最先到达）。

### auth

yes

### response

Status: 200
	
	unread_count: @number 本次获取后该chat_id中剩余的未读消息数量。

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

## 获取两名用户之间的消息（通过user_id）

`GET /chats_v2/:user_a_id/:user_b_id`

a与b的顺序不敏感。

其余同`通过chat_id获取`的接口。

该接口可视为`根据聊天双方user_id获取chat_id`与`获取两名用户之间的消息（通过chat_id）`接口的合并操作。

## 删除聊天（室） delete chat

`DELETE /chats_v2/:chat_id`

此接口用于删除聊天室。聊天双方中任意一方执行删除操作后，双方的聊天信息都将被永久删除，未读消息也将不被保留。

### auth

yes

### response

Status: 204

## 根据聊天双方user_id获取chat_id

`GET /chats_v2/users/:user_a_id/:user_b_id`

a与b的顺序不敏感。

### auth

yes

### response

Status: 200

	{
		"chat_id": @number
	}
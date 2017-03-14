# 其他接口

## 同步消息 :white_check_mark:

`GET /sync`

用于获取同步消息数量（即是否有新的同步消息），也可用于判断是否已经连接（比如重新进入app后判断用户是否在登陆状态）。

### auth

yes

### response

Status: 200

	{
		"friend_request": @number, 好友请求数量
		"chat": @number, 未读消息数量
		"chat_room": @number 未读聊天室消息数量
	}

## 反馈接口 :white_check_mark:

`POST /feedback`

该接口用于用户消息反馈（后端会一并关联用户联系方式，如email和cellphone）。

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| type | string('feedback','report','tag') | 具体反馈类型 |
| content | string(500) | 反馈内容 |

目前，report发送至support@letsfae.com，feedback / tag发送至feedback@letsfae.com。

### response

Status: 201

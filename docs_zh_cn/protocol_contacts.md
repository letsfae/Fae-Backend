# 联系人类接口

联系人contacts级接口（总接口）包含三种关系，friends，follows及blocks。

## 发起好友请求

`POST /friends/request`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| requested_user_id | number | 被请求用户id |
| resend (optional) | boolean | 重发好友请求，默认为false |

resend设为true后会向被请求方客户端重新推送好友请求。

### response

Status: 201

## 确认好友请求 :white_check_mark:

`POST /friends/accept`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| friend_request_id | number | 好友请求id |

### response

Status: 201

## 忽略好友请求 :white_check_mark:

`POST /friends/ignore`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| friend_request_id | number | 好友请求id |

### response

Status: 201

## 撤销好友请求

`POST /friends/withdraw`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| friend_request_id | number | 好友请求id |

撤销后，对方不会收到撤销的push notification。

### response

Status: 201

## 删除好友 :white_check_mark:

`DELETE /friends/:user_id`

### auth

yes

### response

Status: 204

## 获取所有好友请求 :white_check_mark:

`GET /friends/request`

当sync接口中得到friend_request数量不为0的时候，可请求该接口获取所有朋友请求。

### auth

yes

### response

Status: 200

	[
		{
			"friend_request_id": @number,
			"request_user_id": @number,
			"request_user_name": @string (if show_user_name is true, else null),
			"request_user_nick_name": @string,
			"request_user_age": @number (if show_age is true, else null),
			"request_user_gender": @string (if show_gender is true, else null),
			"request_email": @string,
			"created_at": @string
		},
		...		
	]

此处冗余了user_name及email，方便显示。

## 获取好友列表

`GET /friends`

### auth

yes

### response

	[
		{
			"friend_id": @number,
			"friend_user_name": @string (if show_user_name is true, else null),
			"friend_user_nick_name": @string,
			"friend_user_age": @number (if show_age is true, else null),
			"friend_user_gender": @string (if show_gender is true, else null),
		},
		...
	]

## 关注某人

`POST /follows`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| followee_id | number | 被follow人的id |

### response

Status: 201

## 关注user的人（user被关注）

`GET /follows/:user_id/follower`

### auth

yes

### response

	[
		{
			"follower_id": @number,
			"follower_user_name": @string,
			"follower_user_name": @string (if show_user_name is true, else null),
			"follower_user_nick_name": @string,
			"follower_user_age": @number (if show_age is true, else null),
			"follower_user_gender": @string (if show_gender is true, else null),
		},
		...
	]

## user关注的人

`GET /follows/:user_id/followee`

### auth

yes

### response

	[
		{
			"followee_id": @number,
			"followee_user_name": @string,
			"followee_user_name": @string (if show_user_name is true, else null),
			"followee_user_nick_name": @string,
			"followee_user_age": @number (if show_age is true, else null),
			"followee_user_gender": @string (if show_gender is true, else null),
		},
		...
	]

## 取消关注某人

`DELETE /follows/:followee_id`

### auth

yes

### response

Status: 204

## 屏蔽某人 :white_check_mark:

`POST /blocks`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| user_id | number | 被屏蔽用户id |

调用该接口后：

- 如果已经是好友，则好友关系解除且不可再加为好友。
- 如果不是好友，则不可加为好友。

### response

Status: 201

## 解除屏蔽 :white_check_mark:

`DELETE /blocks/:user_id`

### auth

yes

### response

Status: 204
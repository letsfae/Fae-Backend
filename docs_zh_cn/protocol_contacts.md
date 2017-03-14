# 联系人类接口

联系人contacts级接口（总接口）包含三种关系，friends，follows及blocks。

## 发起好友请求 :white_check_mark:

`POST /friends/request`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| requested_user_id | number | 被请求用户id |

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
			"request_user_name": @string,
			"request_email": @string,
			"created_at": @string
		},
		...		
	]

此处冗余了user_name及email，方便显示。

## 获取好友列表

...

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
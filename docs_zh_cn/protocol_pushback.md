# 反推接口

当有相关事件产生后，服务器会主动反推消息（仅对`is_mobile`为true的设备推送）。如果客户端没有注册device_id，则无法收到反推消息，此时可通过sync接口主动查看是否存在未处理消息（或直接通过其他接口调用返回的错误判断当前状态）。

所有反推消息格式均为json，会使用type字段标识具体的反推类型。

## 用户从其他设备登陆 authentication other device :white_check_mark:

	{
		"type": "authentication_other_device",
		"device_id": @number 其他设备的设备id（该字段可能为空）,
		"fae_client_version": @string 其他设备客户端版本号,
		"auth": @boolean 如果为true，说明用户登录仍然合法，否则说明已经被挤下线（此时auth已经失效）
	}

## 好友请求 friends new request

	{
		"type": "friends_new_request",
		"request_user_id": @number 请求用户
	}

## 好友请求回复 friends request reponse

	{
		"type": "friends_request_reponse",
		"requested_user_id": @number 被请求用户,
		"result": @string("accept","ignore")
	}

## 新消息 :white_check_mark:

	{
		"type": "chat_new_message",
		"chat_id": @number,
		"last_message": @string,
		"last_message_sender_id": @number,
		"last_message_timestamp": @string,
		"last_message_type": @string,
		"unread_count": @number
	}

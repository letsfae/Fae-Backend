# 接口概况

本接口用于Fae App前后端通信。通信协议采用应用层协议HTTP(s)。

Base URL：`https://api.letsfae.com/`

## 版本号

根据rest标准，版本信息需要标注在header中。如果无版本号，默认为最新版本（建议手动维护版本号）。

`Accept: application/x.faeapp.v1+json`

版本号形式为`v1`, `v2`, `v3`... 只有major，minor更新需要在原版本中自行维护。

## 编码

- request及response编码为utf-8。
- response返回格式均为json。
- request的body如果为json会特殊注明，否则为x-www-form-urlencoded（`Content-Type: application/x-www-form-urlencoded`）。

## 参数及过滤信息

参数通过request body或get参数实现，具体参见接口功能。

- GET的filters在url参数中，如`/xxxxx?param1=AAA&param2=BBB`。注意url需要使用urlencode编码。
- POST/PUT/DELETE的parameters内容在header中。

所有参数，如果为可选（optional），则可以不存在（有默认值）；但如果设置了key，则value必须存在（除非另有说明）。

## 状态码

接口调用成功后，如果成功则返回2xx。如果有错误，错误在error字段中。

- 200 OK [GET]：服务器成功返回用户请求的数据，该操作是幂等的（Idempotent）。
- 201 Created [POST/PUT/PATCH]：用户新建或修改数据成功。
- 202 Accepted [*]：表示一个请求已经进入后台排队（异步任务）
- 204 No Content [DELETE]：用户删除数据成功。
- 400 Invalid Request [POST/PUT/PATCH]：用户发出的请求有错误，服务器没有进行新建或修改数据的操作，该操作是幂等的。
- 401 Unauthorized [*]：表示用户没有权限（令牌、用户名、密码错误）。
- 403 Forbidden [*] 表示用户得到授权（与401错误相对），但是访问是被禁止的。
- 404 Not Found [*]：用户发出的请求针对的是不存在的记录，服务器没有进行操作，该操作是幂等的。
- 406 Not Acceptable [GET]：用户请求的格式不可得（比如用户请求JSON格式，但是只有XML格式）。
- 410 Gone [GET]：用户请求的资源被永久删除，且不会再得到的。
- 422 Unprocesable Entity [POST/PUT/PATCH] 当创建一个对象时，发生一个验证错误。
- 500 Internal Server Error [*]：服务器发生错误，用户将无法判断发出的请求是否成功。

## 身份验证

登陆成功后会返回user_id和token。

所有需要身份验证的request需要带有auth header。 Fae的auth header构造：

`Authorization: FAE base64(user_id:token:session_id)`

## 开放接口

目前无三方客户端，暂不讨论该情况。

对于Fae自身客户端：

- 在header中`User-Agent`字段值需标注设备（如iphone4, iphone6s, nexus6...）。
- 在header中`Fae-Client-Version`字段为客户端版本（如ios-0.0.1）。

## 错误返回

如果出现错误，http header status code将为4XX或5XX的形式。body中有如下json对象返回（其中errors中为具体错误字段）。

	{
		"status_code": @number,
		"message": @string,
		"errors": {}
	}

# 接口功能

- [用户及认证类](protocol_users.md)
- [地图及各类Pin](protocol_maps.md)
- [用户对于pin的操作](protocol_pins.md)
- [好友及聊天类](protocol_chats.md)
- [文件类](protocol_files.md)
- [富文本](protocol_richtext.md)
- 其他（如下

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

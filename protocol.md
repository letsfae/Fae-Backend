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

## 注册 Sign up :white_check_mark:

`POST /users`

### auth

no

### parameters

| Name | Type | Description |
| --- | --- | --- |
| password | string(8-16) | 密码 |
| email | string(50) | 电邮 |
| first_name | string(50) | 名字 |
| last_name | string(50) | 姓氏 |
| birthday | string(YYYY-MM-DD) | 生日 |
| gender | string("male", "female") | 性别 |

### response

Status: 201

## 登陆 :white_check_mark:

`POST /authentication`

### auth

no

### parameters

| Name | Type | Description |
| --- | --- | --- |
| user_name | string(30) | 用户名 |
| email | string(50) | 电邮 |
| password | string(8-16) | 密码 |
| device_id(optional) | string(1~150) | 设备id，默认为空 |
| is_mobile(optional) | boolean | 是否为移动端，默认为false |

此处用户名和电邮选一个即可（OR关系，另一个字段不用），如果同时存在，以email为准。

device_id用于服务器向客户端做pushback notification，如果为空（或者不存在）则不推送。

is_mobile如果为true，则会踢掉用当前账号登陆的另一台移动设备（非mobile设备不受影响）。

### response

Status: 201

	{
		"user_id": @number
		"token": @string
		"session_id": @number
	}


## 登出 logout :white_check_mark:

`DELETE /authentication`

### auth

yes

### response

Status: 204

## 获取重置登陆的Email :white_check_mark:

`POST /reset_login/code`

### auth

no

### parameters

| Name | Type | Description |
| --- | --- | --- |
| email | string(50) | 电邮 |

code有效时长为发送出来后的30分钟，30分钟内再次获取code为原code。

### response

Status: 201

## 验证重置登陆code :white_check_mark:

`POST /reset_login/code/verify`

### auth

no

### parameters

| Name | Type | Description |
| --- | --- | --- |
| email | string(50) | 电邮 |
| code | string(6) | 邮件中的6位验证数字（用字符串形式传递） |

### response

Status: 201

## 验证code后重置密码 :white_check_mark:

`POST /reset_login/password`

### auth

no

### parameters

| Name | Type | Description |
| --- | --- | --- |
| email | string(50) | 电邮 |
| code | string(6) | 邮件中的6位验证数字（用字符串形式传递） |
| password | string(8-16) | 密码 |

### response

Status: 201

## 验证email是否存在 :white_check_mark:

`GET /existence/email/:email`

### auth

no

### response

Status: 200

	{
		"existence": @boolean
	}

## 验证user name是否存在 :white_check_mark:

`GET /existence/user_name/:user_name`

### auth

no

### response

Status: 200

	{
		"existence": @boolean
	}

## 获取用户账户信息 get account

`GET /users/account`

### auth

yes

### response

Status: 200

	{
		"email": @string,
		"user_name": @string,
		"first_name": @string,
		"last_name": @string,
		"gender": @string,
		"birthday": @string,
		"fae_number": @string(xxx-xxx-xxx),
		"phone": @string(xxx-xxx-xxxx)
	}

## 更新账户信息 update account

`POST /users/account`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| first_name | string(50) | 名字 |
| last_name | string(50) | 姓氏 |
| birthday | string(YYYY-MM-DD) | 生日 |
| gender | string("male", "female") | 性别 |
| phone | string(xxx-xxx-xxxx) | 电话 |
| fae_number | string(xxx-xxx-xxx) | fae number |
| user_name | string(30) | 用户名 |
| email | string(50) | 电邮 |

所有字段均为可选，但必须至少包含一个字段。

### response

Status: 201

## 更新自己的密码 update password

`POST /users/account/password`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| old_password | string(8-16) | 老密码 |
| new_password | string(8-16) | 新密码 |

### response

Status: 201

## 获取用户自己的资料 get self profile (待定)

`GET /users/profile`

### auth

yes

### response

Status: 200

	{
		"mini_avatar": @number 地图上显示的用户小头像，未设置则默认为0
	}

## 获取其他用户资料 get profile (待定)

`GET /users/:user_id/profile`

其余同get self profile。

需要注意的是，获取到的字段仅包含用户设定为公开的字段。

## 更新自己的资料 update self profile (待定)

`POST /users/profile`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| mini_avatar | number | 地图头像小图标 |

所有字段均为可选，但必须至少包含一个字段。

### response

Status: 201

## 获取用户自己的资料隐私设定 get self profile privacy (待定)

`GET /users/profile/privacy`

### auth

yes

### response

Status: 200

	{
		"name": @boolean
	}

## 更新自己的资料隐私设定 update self profile privacy (待定)

`POST /users/profile/privacy`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| name | boolean | 默认为true |

所有字段均为可选，但必须至少包含一个字段。

### response

Status: 201

## 获取用户自己的状态 get self status

`GET /users/status`

### auth

yes

### response

Status: 200

	{
		"status": @number 0~5分别表示offline/online/no distrub/busy/away/invisible,
		"message": @string
	}

## 获取其他用户状态 get status

`GET /users/:user_id/status`

基本同get self status。需要注意的是，获取其他用户的状态时（自身user_id除外），该用户的invisible状态将无法获取到（即使该用户状态为invisible，返回状态仍为offline）。

## 更新自己的状态 update self status

`POST /users/status`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| status | number | 0:offline, 1:online, 2:no distrub, 3:busy, 4:away, 5:invisible |
| message | string(100) | 短状态（可以为空） |

所有字段均为可选，但必须至少包含一个字段。

### response

Status: 201

## 设置头像 set self avatar :white_check_mark:

`POST /files/avatar`

### auth

yes

### parameters

类型为form-data。

| Name | Description |
| --- | --- |
| avatar | 图片内容 |

图片格式必须为jpeg，大小为500x500px。

### response

Status: 201

## 获取头像 get self avatar :white_check_mark:

`GET /files/avatar`

### auth

yes

### response

Status: 200

Body图片数据，其中`Content-Type`为`image/jpeg`。

## 获取其他用户头像 get avatar :white_check_mark:

`GET /files/avatar/:user_id`

其余同get self profile。

## 同步消息 :white_check_mark:

`GET /sync`

用于获取同步消息数量（即是否有新的同步消息），也可用于判断是否已经连接（比如重新进入app后判断用户是否在登陆状态）。

### auth

yes

### response

Status: 200

	{
		"friend_request": @number 好友请求数量,
		"chat": @number 未读消息数量
	}

## 更新用户自身的当前坐标 :white_check_mark:

`POST /map/user`

每隔一段固定时间跟新一次。只有移动设备有权限更新坐标，其余设备无权限。

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| geo_latitude | number | 纬度 |
| geo_longitude | number | 经度 |

### response

Status: 201

如果返回422，可能原因是当前并非移动设备。

## 获取地图数据 :white_check_mark:

`GET /map`

### auth

yes

### filters

| Name | Type | Description |
| --- | --- | --- |
| geo_latitude | number | 中心点纬度 |
| geo_longitude | number | 中心点经度 |
| radius (optional) | number | 半径，默认值为200m |
| type (optional) | string("user","comment") | 筛选类型，默认为所有，类型之间用逗号隔开 |
| max_count (optional) | number | 返回节点最大数量，默认为30，最大为100） |

对于一直在更新的user点，可以每隔一段时间获取一次。

### response

Status: 200

	[
		{
			"type": @string,
			"geolocation": {
				"latitude": @number,
				"longitude": @number
			},
			"created_at": @string
			...
		},
		{...},
		{...}
	]

返回一个array, 每个object一定包含type，geolocation和created_at，其他内容依据type决定（可参见具体类型的相关接口）。

对于user类型的点，考虑到用户隐私问题，服务器会返回5个一定范围内的随机点, 格式如下：

	{
		"type": "user",
		"user_id": @number,
		"geolocation": [
			{
				"latitude": @number,
				"longitude": @number
			},
			{...},
			{...},
			{...},
			{...}
		]
	}

## 发布comment :white_check_mark:

`POST /comments`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| content | text | 内容 |
| geo_latitude | number | 纬度 |
| geo_longitude | number | 经度 |

### response

Status: 201

	{
		"comment_id": @number
	}


## 获取comment :white_check_mark:

`GET /comments/:comment_id`

### auth

yes

### response

Status: 200

	{
		"comment_id": @number,
		"user_id": @number
		"content": @string,
		"geolocation": {
			"latitude": @number,
			"longitude": @number
		},
		"created_at": @string
	}

## 获取某个用户的所有comment :white_check_mark:

`GET /comments/users/:user_id`

### auth

yes

### filters

| Name | Type | Description |
| --- | --- | --- |
| start_time | string(YYYY-MM-DD hh:mm:ss) | 时间范围，默认为1970-01-01 00:00:00 |
| end_time | string(YYYY-MM-DD hh:mm:ss) | 时间范围，默认为当前日期和时间 |
| page | number | 页数，默认为第1页（头30条） |

过滤参数均为可选。

### response

Status: 200

	{
		page: @number,
		total_pages: @number,
		comments: [
			{...},
			{...}
		]
	}

具体数组内对象同“获取comment”所得到的对象。

## 删除comment :white_check_mark:

`DELETE /comments/:comment_id`

### auth

yes

### response

Status: 204

## 发起好友请求

`POST /friends/request`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| requested_user_id | number | 被请求用户id |

### response

Status: 201

## 确认好友请求

`POST /friends/accept`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| friend_request_id | number | 好友请求id |

### response

Status: 201

## 忽略好友请求

`POST /friends/ignore`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| friend_request_id | number | 好友请求id |

### response

Status: 201

## 删除好友

`DELETE /friends/:user_id`

### auth

yes

### response

Status: 204

## 获取所有好友请求

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

# 反推接口

当有相关事件产生后，服务器会主动反推消息。如果客户端没有注册device_id，则无法收到反推消息，此时可通过sync接口主动查看是否存在未处理消息（或直接通过其他接口调用返回的错误判断当前状态）。

所有反推消息格式均为json，会使用type字段标识具体的反推类型。

## 用户从其他设备登陆 authentication other device

	{
		"type": "authentication_other_device",
		"device_id": @number 其他设备的设备id（该字段可能为空）,
		"fae_client_version": @string 其他设备客户端版本号,
		"user_agent": @string 其他设备客户端标识,
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

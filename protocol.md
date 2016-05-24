# 接口概况

本接口用于FaeApp前后端通信。通信协议采用应用层协议HTTP(s)。

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

## 状态码

接口调用成功后，如果成功则返回2xx。如果有错误，错误在error字段中。

- 200 OK [GET]：服务器成功返回用户请求的数据，该操作是幂等的（Idempotent）。
- 201 CREATED [POST/PUT/PATCH]：用户新建或修改数据成功。
- 202 Accepted [*]：表示一个请求已经进入后台排队（异步任务）
- 204 NO CONTENT [DELETE]：用户删除数据成功。
- 400 INVALID REQUEST [POST/PUT/PATCH]：用户发出的请求有错误，服务器没有进行新建或修改数据的操作，该操作是幂等的。
- 401 Unauthorized [*]：表示用户没有权限（令牌、用户名、密码错误）。
- 403 Forbidden [*] 表示用户得到授权（与401错误相对），但是访问是被禁止的。
- 404 NOT FOUND [*]：用户发出的请求针对的是不存在的记录，服务器没有进行操作，该操作是幂等的。
- 406 Not Acceptable [GET]：用户请求的格式不可得（比如用户请求JSON格式，但是只有XML格式）。
- 410 Gone [GET]：用户请求的资源被永久删除，且不会再得到的。
- 422 Unprocesable entity [POST/PUT/PATCH] 当创建一个对象时，发生一个验证错误。
- 500 INTERNAL SERVER ERROR [*]：服务器发生错误，用户将无法判断发出的请求是否成功。

## 身份验证

登陆成功后会返回user_id和token。

所有需要身份验证的request需要带有auth header。 Fae的auth header构造：

`Authorization: FAE base64(user_id:token:session_id)`

## 开放接口

目前无三方客户端，暂不讨论该情况。

对于Fae自身客户端：

- 在header中`User-Agent`字段值需标注设备（如iphone4, iphone6s, nexus6...）。
- 在header中`Fae-Client-Version`字段为客户端版本（如ios-0.0.1）。
- 在header中`Device-ID`字段为设备ID。

## 错误返回

如果出现错误，http header status code将为4XX或5XX的形式。body中有如下json对象返回（其中errors中为具体错误字段）。

	{
		"status_code": @number,
		"message": @string,
		"errors": {}
	}

# 接口功能

## 注册 Sign up

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


## 登陆 Login

`POST /authentication`

### auth

no

### parameters

| Name | Type | Description |
| --- | --- | --- |
| user_name | string(30) | 用户名 |
| email | string(50) | 电邮 |
| password | string(8-16) | 密码 |

此处用户名和电邮选一个即可（OR关系，另一个字段不用），如果同时存在，以email为准。

### response

Status: 201

	{
		"user_id": @number
		"token": @string
		"session_id": @number
	}

### request example

Header
	
	POST /authentication HTTP/1.1
	Accept: application/x.faeapp.v1+json
	Content-Type: application/x-www-form-urlencoded
	User-Agent: iphone6s
	Fae-Client-Version: ios-0.0.1
	Device-ID: gu3v0KaU7jLS7SGdS2Rb
	
Body

	name: test
	email: test@letsfae.com
	password: 123456	

## 登出 logout

`DELETE /authentication/:user_id`

### auth

yes

### response

Status: 204

### request example

Header
	
	DELETE /authentication/1 HTTP/1.1
	Accept: application/x.faeapp.v1+json
	User-Agent: iphone6s
	Fae-Client-Version: ios-0.0.1
	Device-ID: gu3v0KaU7jLS7SGdS2Rb
	Authorization: FAE MToxMjM0NTY6MQ==

## 获取重置登陆的Email

`POST /reset_login/code`

### auth

no

### parameters

| Name | Type | Description |
| --- | --- | --- |
| email | string(50) | 电邮 |

### response

Status: 201

## 验证重置登陆code

`PUT /reset_login/code`

### auth

no

### parameters

| Name | Type | Description |
| --- | --- | --- |
| email | string(50) | 电邮 |
| code | string(6) | 邮件中的6位验证数字（用字符串形式传递） |

code有效时长为发送出来后的30分钟。

### response

Status: 201

## 验证code后重置密码

`PUT /reset_login/password`

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


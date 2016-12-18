# 用户及认证类接口

## 注册 Sign up :white_check_mark:

`POST /users`

### auth

no

### parameters

| Name | Type | Description |
| --- | --- | --- |
| password | string(8-16) | 密码 |
| email | string(50) | 电邮 |
| user_name | string(20) | 用户名 |
| first_name | string(50) | 名字 |
| last_name | string(50) | 姓氏 |
| birthday | string(YYYY-MM-DD) | 生日 |
| gender | string('male','female') | 性别 |

user_name格式要求为：仅可包含大小写字母、数字及下划线，长度3-20，对字母大小写不敏感（即显示区分大小写，但AAA与aAa视为相同用户名）。

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

device_id用于服务器向客户端做pushback notification，如果不存在则不推送。

is_mobile如果为true，则会踢掉用当前账号登陆的另一台移动设备（非mobile设备不受影响）。

如果相同device_id账号登陆不同用户，前一个用户会被挤下线。

login出现6次错误后用户账户将被永久禁止登陆（即第7次无法登陆），解禁需调用reset_login接口。

### response

Status: 201

	{
		"user_id": @number
		"token": @string
		"session_id": @number
	}

错误后会返回login_count。

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

## 获取用户账户信息 get account :white_check_mark:

`GET /users/account`

### auth

yes

### response

Status: 200

	{
		"email": @string,
		"email_verified": @boolean,
		"user_name": @string,
		"first_name": @string,
		"last_name": @string,
		"gender": @string,
		"birthday": @string,
		"phone": @string(xxx-xxx-xxxx),
		"phone_verified": @boolean,
		"mini_avatar": @number, 地图上显示的用户小头像，未设置则默认为0
		"last_login_at": @string
	}

## 更新账户信息 update account :white_check_mark:

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
| user_name | string(30) | 用户名（该接口可能会被单独提取并设置） |
| mini_avatar | integer | 地图上显示的用户小头像 |

所有字段均为可选，但必须至少包含一个字段。这些接口没有特殊操作（有特殊操作的请使用特定接口，如更新password）。

需要注意的是，user_name格式要求同用户注册。

### response

Status: 201

## 测试自身密码是否正确 verify password :white_check_mark:

`POST /users/account/password/verify`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| password | string(8-16) |密码 |

密码验证如果6次错误，则自动锁定并退出（Auth失效）。解锁需使用reset login的接口。

### response

Status: 201

错误后会返回login_count。

## 更新自己的密码 update password :white_check_mark:

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

错误后会返回login_count。

## 更新自己的邮箱 update email :white_check_mark:

`POST /users/account/email`

更新email后新邮箱会收到验证码，需调用verify email接口完成email验证。code有效时长为发送出来后的30分钟。

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| email | string(50) | 新email地址 |

### response

Status: 201

## 验证邮箱 verify email :white_check_mark:

`POST /users/account/email/verify`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| email | string(50) | 新email地址 |
| code | string(6) | 6位验证数字，以字符串形式传递 |

### response

Status: 201

## 更新自己的电话 update phone :white_check_mark:

`POST /users/account/phone`

更新phone number后该号码手机会收到验证码，需调用verify phone接口完成phone验证。code有效时长为发送出来后的30分钟。

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| phone | string(xxx-xxx-xxxx) | 新电话 |

### response

Status: 201

## 验证电话 verify phone :white_check_mark:

`POST /users/account/phone/verify`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| phone | string(xxx-xxx-xxxx) | 新电话 |
| code | string(6) | 6位验证数字，以字符串形式传递 |

### response

Status: 201

----------

注意profile级接口和account级接口的区别：account接口只能用户自身set/get，主要负责基础用户信息及密码的维护；profile接口可以由用户自身set/get并被其他用户get，profile接口中不光可以设置除了account接口之外的字段，同时也作为account接口权限包装。

----------

## 获取用户自己的资料 get self profile :white_check_mark:

`GET /users/profile`

### auth

yes

### response

Status: 200

	{
		"user_id": @number,
		"user_name": @string,
		"mini_avatar": @number，
		"birthday": @string,
		"email": @boolean,
		"phone": @boolean,
		"gender": @boolean
	}

## 获取其他用户资料 get profile :white_check_mark:

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
| xxx | number | xxx |

所有字段均为可选，但必须至少包含一个字段。

### response

Status: 201

## 获取用户自己的资料隐私设定 get self profile privacy

`GET /users/profile/privacy`

### auth

yes

### response

Status: 200

	{
		"show_user_name": @boolean,
		"show_email": @boolean,
		"show_phone": @boolean,
		"show_birthday": @boolean,
		"show_gender": @boolean
	}

## 更新自己的资料隐私设定 update self profile privacy

`POST /users/profile/privacy`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| show_user_name | boolean | 显示用户名 |
| show_email | boolean | 显示email |
| show_phone | boolean | 显示电话 |
| show_birthday | boolean | 显示生日 |
| show_gender | boolean | 显示性别 |

所有字段均为可选，但必须至少包含一个字段。

默认所有字段均为true。

### response

Status: 201

## 获取用户自己的状态 get self status :white_check_mark:

`GET /users/status`

### auth

yes

### response

Status: 200

	{
		"status": @number 0~5分别表示offline/online/no distrub/busy/away/invisible,
		"message": @string
	}


一个用户的状态在不同设备之间共享。

用户的状态不被服务器保留：即当用户的第一台设备登陆时，状态置位为online，最后一台设备退出时，状态置位为offline。

## 获取其他用户状态 get status :white_check_mark:

`GET /users/:user_id/status`

基本同get self status。

需要注意的是，获取其他用户的状态时（自身user_id除外），该用户的invisible状态将无法获取到（即使该用户状态为invisible，返回状态仍为offline）。

## 更新自己的状态 update self status :white_check_mark:

`POST /users/status`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| status | number | 0:offline, 1:online, 2:no distrub, 3:busy, 4:away, 5:invisible |
| message | string(100) | 短状态（可以为空） |

所有字段均为可选，但必须至少包含一个字段。

用户状态为offline/invisible时，map中也不会显示该用户的user pin。

### response

Status: 201

## 获取某个用户NameCard :white_check_mark:

`GET /users/:user_id/name_card`

### auth

yes

### response

Status: 200

	{
		"nick_name": @string,
		"short_intro": @string,
		"tags": [
			{
				"tag_id": @number,
				"title": @string,
				"color": @string
			},
			{...},
			{...}
		],
		"show_gender": @boolean,
		"show_age": @boolean,
		"gender": @string 同account中的设置，当且仅当show_gender为true时才具有该字段
		"age": @string 同account中的设置(通过birthday计算得来)，当且仅当show_age为true时才具有该字段
	}

## 获取自己的NameCard :white_check_mark:

`GET /users/name_card`

其余同获取某个用户NameCard。

## 获取所有NameCard所属的tag :white_check_mark:

`GET /users/name_card/tags`

此接口用于获得所有系统内置的namecard的tag。

### auth

yes

### response

Status: 200

	[
		{
			"tag_id": @number,
			"title": @string,
			"color": @string
		},
		{...},
		{...}
	]

## 更新NameCard :white_check_mark:

`POST /users/name_card`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| nick_name | string(50) | 昵称 |
| short_intro | string(200) | 短介绍（可为空） |
| tag_ids | number | 所有tag的id，使用;分割，最多3个tag |
| show_age | boolean | 是否显示年龄 |
| show_gender | boolean | 是否显示性别 |

必须出现以上至少一个字段。

### response

Status: 201

## 保存NameCard :white_check_mark:

`POST /users/:user_id/name_card/save`

### auth

yes

### response

Status: 201

## 取消保存NameCard :white_check_mark:

`DELETE /users/:user_id/name_card/save`

### auth

yes

### response

Status: 204

## 获取所有保存的namecard :white_check_mark:

`GET /users/name_card/saved`

### auth

yes

### response

Status: 200

	[
		{
			"name_card_user_id": @number,
			"created_at": @string
		},
		{...},
		{...}
	]


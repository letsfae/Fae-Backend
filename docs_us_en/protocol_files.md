# 文件类接口

除非特别说明，否则上传时parameters的类型为form-data，特别注意此时Content-Type不要设置。

# 文件类通用接口

利用通用接口上传文件后，如若一段时间未得到引用（指某种pin引用该文件），则该文件会被永久删除。

## 上传文件 :white_check_mark:

`POST /files`

### auth

yes

### parameters

| Name | Description |
| --- | --- |
| file | 文件内容（采用mine形式，因此带有文件名及扩展名） |
| type | string(image,video) |
| description (optional) | 语义化的文件描述 |
| custom_tag (optional) | 标签化的文件描述 |

文件大小需小于10MB。

### response

Status: 201

	{
		"file_id": @number
	}

## 获取文件内容 :white_check_mark:

`GET /files/:file_id/data`

### auth

no

### response

Status: 200

body为文件内容。

## 获取文件属性 :white_check_mark:

`GET /files/:file_id/attribute`

### auth

yes

### response

Status: 200

	{
		"file_id": @number,
		"file_name": @string,
		"created_at": @string,
		"type": @string,
		"mine_type": @string,
		"description": @string,
		"custom_tag": @string
	}

# 文件类专项接口

## 设置头像 set self avatar :white_check_mark:

`POST /files/users/avatar`

### auth

yes

### parameters

| Name | Description |
| --- | --- |
| avatar | 图片内容 |

图片格式必须为jpeg，大小为500x500px。

### response

Status: 201

## 获取头像 get self avatar :white_check_mark:

`GET /files/users/avatar`

### auth

yes

### response

Status: 200

Body图片数据，其中`Content-Type`为`image/jpeg`。

## 获取其他用户头像 get avatar :white_check_mark:

`GET /files/users/:user_id/avatar`

其余同get self avatar。

## 设置NameCard背景图片 :white_check_mark:

`POST /files/users/name_card_cover`

### auth

yes

### parameters

| Name | Description |
| --- | --- |
| name_card_cover | 图片内容 |

图片格式必须为jpeg。

### response

Status: 201

## 获取NameCard背景图片 :white_check_mark:

`GET /files/users/name_card_cover`

### auth

yes

### response

Status: 200

Body图片数据，其中`Content-Type`为`image/jpeg`。

## 获取其他用户NameCard背景图片 :white_check_mark:

`GET /files/users/:user_id/name_card_cover`

其余同或者NameCard背景图片。

## 设置NameCard图片 :white_check_mark:

`POST /files/users/name_card_photo`

### auth

yes

### parameters

| Name | Description |
| --- | --- |
| position | 图片位置（1-8） |
| photo | 图片内容 |

图片位置决定了更新哪个位置的图片得到更新，后端不会对图片进行向前填充（即位置1不存在时位置2图片向前填充）。

图片大小需小于4MB。

### response

Status: 201

## 删除NameCard图片 :white_check_mark:

`DELETE /files/users/name_card_photo/:position`

### auth

yes

### response

Status: 204

## 获取NameCard指定位置图片 :white_check_mark:

`GET /files/users/:user_id/name_card_photo/:position`

### auth

no

### response

Status: 200

Body图片数据。

## 获取自身的NameCard指定位置图片 :white_check_mark:

`GET /files/users/name_card_photo/:position`

### auth

yes

其余同通用获取NameCard指定位置图片的接口。

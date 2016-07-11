# 文件类接口

## 设置头像 set self avatar :white_check_mark:

`POST /files/users/avatar`

### auth

yes

### parameters

类型为form-data（特别注意此时Content-Type不要设置）。

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

*此处应前端要求，不需要auth即可使用该接口。

## 设置NameCard图片

`POST /files/users/name_card_photo`

### auth

yes

### parameters

类型为form-data（特别注意此时Content-Type不要设置）。

| Name | Description |
| --- | --- |
| position | 图片位置（1-8） |
| photo | 图片内容 |

图片位置决定了更新哪个位置的图片得到更新，后端不会对图片进行向前填充（即位置1不存在时位置2图片向前填充）。

### response

Status: 201

## 删除NameCard图片

`DELETE /files/users/name_card_photo/:position`

### auth

yes

### response

Status: 204

## 获取NameCard指定位置图片

`GET /files/users/:user_id/name_card_photo/:position`

### auth

no

### response

Status: 200

Body图片数据。

## 获取自身的NameCard指定位置图片

`GET /files/users/name_card_photo/:position`

### auth

yes

其余同通用获取NameCard指定位置图片的接口。

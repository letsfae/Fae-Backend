
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
| duration | number | 持续显示时间，前端需默认为180,单位为min |
| interaction_radius (optional) | number | 交互范围，默认不存在，单位m |
| anonymous (optinal) | boolean | 匿名，默认为false |

### response

Status: 201

	{
		"comment_id": @number
	}

## 更新comment :white_check_mark:

`POST /comments/:comment_id`

### auth

yes

### parameters

同发布comment，但所有参数均为可选。

### response

Status: 201

## 获取comment :white_check_mark:

`GET /comments/:comment_id`

### auth

yes

### response

Status: 200

	{
		"comment_id": @number,
		"user_id": @number, 如果非自身创建的pin且anonymous为true，则user_id为null
		"nick_name": @string,
		"anonymous": @boolean,
		"content": @string,
		"geolocation": {
			"latitude": @number,
			"longitude": @number
		},
		"created_at": @string,
		"liked_count": @number,
		"saved_count": @number,
		"comment_count": @number,
		"feelings_count": [
			@number,
			...
		],
		"user_pin_operations": {
			"is_read": @boolean, 对当前用户是否已读
			"read_timestamp": @string,
			"is_liked": @boolean, 对当前用户是否点赞
			"liked_timestamp": @string,
			"is_saved": @boolean 对当前用户是否收藏
			"saved_timestamp": @string,
			"feeling": @number,
			"feeling_timestamp": @string,
		}
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

该user发布的anonymous为true的pin将不会被获取（自身的pin除外）。

### response

Status: 200

	page: @number
	total_pages: @number

	-----

	[
		{...},
		{...}
	]

具体数组内对象同“获取comment”所得到的对象。

## 删除comment :white_check_mark:

`DELETE /comments/:comment_id`

### auth

yes

### response

Status: 204

## 发布media :white_check_mark:

`POST /medias`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| file_ids | file_id | 最多6个，通过;区分 |
| tag_ids (optional) | tag_id | 最多50个，通过;区分 |
| description (optinal) | string | 描述 |
| geo_latitude | number | 纬度 |
| geo_longitude | number | 经度 |
| duration | number | 持续显示时间，前端需默认为180,单位为min |
| interaction_radius (optional) | number | 交互范围，默认不存在，单位m |
| anonymous (optinal) | boolean | 匿名，默认为false |

### response

Status: 201

	{
		"media_id": @number
	}

## 更新media :white_check_mark:

`POST /medias/:media_id`

### auth

yes

### parameters

同发布media，但所有参数均为可选。

必须存在至少一个file，因此不允许file_ids置`null`。

如需删除tag_ids，将其置`null`。

### response

Status: 201

## 获取media :white_check_mark:

`GET /medias/:media_id`

### auth

yes

### response

Status: 200

	{
		"media_id": @number,
		"user_id": @number, 如果非自身创建的pin且anonymous为true，则user_id为null
		"nick_name": @string,
		"anonymous": @boolean,
		"file_ids": [
			@number, 
			..., 
			@number
		],
		"tag_ids": [
			@number, 
			..., 
			@number
		],
		"description": @string,
		"geolocation": {
			"latitude": @number,
			"longitude": @number
		},
		"created_at": @string,
		"liked_count": @number,
		"saved_count": @number,
		"comment_count": @number,
		"feelings_count": [
			@number,
			...
		],
		"user_pin_operations": {
			"is_read": @boolean, 对当前用户是否已读
			"read_timestamp": @string,
			"is_liked": @boolean, 对当前用户是否点赞
			"liked_timestamp": @string,
			"is_saved": @boolean 对当前用户是否收藏
			"saved_timestamp": @string,
			"feeling": @number,
			"feeling_timestamp": @string,
		}
	}

## 获取某个用户的所有media :white_check_mark:

`GET /medias/users/:user_id`

### auth

yes

### filters

| Name | Type | Description |
| --- | --- | --- |
| start_time | string(YYYY-MM-DD hh:mm:ss) | 时间范围，默认为1970-01-01 00:00:00 |
| end_time | string(YYYY-MM-DD hh:mm:ss) | 时间范围，默认为当前日期和时间 |
| page | number | 页数，默认为第1页（头30条） |

过滤参数均为可选。

该user发布的anonymous为true的pin将不会被获取（自身的pin除外）。

### response

Status: 200

	page: @number
	total_pages: @number

	-----

	[
		{...},
		{...}
	]

具体数组内对象同“获取media”所得到的对象。

## 删除media :white_check_mark:

`DELETE /medias/:media_id`

### auth

yes

### response

Status: 204

## 发布faevor (待定)

`POST /faevors`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| file_ids(optional) | file_id | 最多5个，通过;区分 |
| tag_ids(optional) | tag_id | 最多50个，通过;区分 |
| budget | integer | 费用，单位为美元 |
| bouns (optional) | string | 奖励的文字描述 |
| name | string | 名字 |
| description (optinal) | string | 描述 |
| due_time | string(YYYY-MM-DD hh:mm:ss) | 终止时间 |
| expire_time | string(YYYY-MM-DD hh:mm:ss) | 过期时间 |
| geo_latitude | number | 纬度 |
| geo_longitude | number | 经度 |

### response

Status: 201

	{
		"faevor_id": @number
	}

## 更新faevor (待定)

`POST /faevors/:faevor_id`

### auth

yes

### parameters

同发布faevor，但所有参数均为可选。

如果需要删除file_ids、tag_ids、bouns，将字段内容置位`null`。

### response

Status: 201

## 获取faevor (待定)

`GET /faevors/:faevor_id`

### auth

yes

### response

Status: 200

	{
		"faevor_id": @number,
		"user_id": @number
		"file_ids": [
			@number, 
			..., 
			@number
		],
		"tag_ids": [
			@number, 
			..., 
			@number
		],
		"description": @string,
		"name": @string,
		"budget": @number,
		"bouns": @string,
		"due_time": @string,
		"expire_time": @string,
		"geolocation": {
			"latitude": @number,
			"longitude": @number
		},
		"created_at": @string
	}

## 获取某个用户的所有faevor (待定)

`GET /faevors/users/:user_id`

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

	page: @number
	total_pages: @number

	-----

	[
		{...},
		{...}
	]

具体数组内对象同“获取media”所得到的对象。

## 删除faevor (待定)

`DELETE /faevors/:faevor_id`

### auth

yes

### response

Status: 204


## Save 保存 （废除）

`POST /pins/:type/:pin_id/save`

其中type可为`media`、`comment`、`place`、`location`。

### auth

yes

### response

Status: 201

## Unsave （废除）

`DELETE /pins/:type/:pin_id/save`

其中type可为`media`、`comment`、`place`、`location`。

### auth

yes

### response

Status: 204
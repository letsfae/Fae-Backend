# 用户对于pin的操作

## 标记已读 :white_check_mark:

`POST /pins/:type/:pin_id/read`

其中type可为`media`、`comment`。

### auth

yes

### response

Status: 201

## Like 点赞 :white_check_mark:

`POST /pins/:type/:pin_id/like`

其中type可为`media`、`comment`。

### auth

yes

### response

Status: 201

## Unlike （注意不是dislike） :white_check_mark:

`DELETE /pins/:type/:pin_id/like`

其中type可为`media`、`comment`。

### auth

yes

### response

Status: 204

## Save 保存 :white_check_mark:

`POST /pins/:type/:pin_id/save`

其中type可为`media`、`comment`、`place`。

### auth

yes

### response

Status: 201

## Unsave :white_check_mark:

`DELETE /pins/:type/:pin_id/save`

其中type可为`media`、`comment`、`place`。

### auth

yes

### response

Status: 204

## Feeling :white_check_mark:

`POST /pins/:type/:pin_id/feeling`

其中type可为`media`、`comment`。

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| feeling | integer(0-10) | feeling表情 |

### response

Status: 201

## Remove Feeling :white_check_mark:

`DELETE /pins/:type/:pin_id/feeling`

其中type可为`media`、`comment`。

### auth

yes

### response

Status: 204

## comment 评论 :white_check_mark:

`POST /pins/:type/:pin_id/comments`

其中type可为`media`、`comment`。

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| content | text | 评论内容 |
| anonymous (optinal) | boolean | 匿名，默认为false |

### response

Status: 201

	{
		"pin_comment_id": @number
	}

## update comment 更新评论

`POST /pins/comments/:pin_comment_id`

### auth

yes

### parameters

同发布pin comment，但所有参数均为可选。

### response

Status: 201

## Uncomment :white_check_mark:

`DELETE /pins/comments/:pin_comment_id`

其中type可为`media`、`comment`。

### auth

yes

### response

Status: 204

## 获取saved pin :white_check_mark:

`GET /pins/saved`

### auth

yes

### filters

| Name | Type | Description |
| --- | --- | --- |
| start_time | string(YYYY-MM-DD hh:mm:ss) | 时间范围，默认为1970-01-01 00:00:00 |
| end_time | string(YYYY-MM-DD hh:mm:ss) | 时间范围，默认为当前日期和时间 |
| page | number | 页数，默认为第1页（头30条） |
| is_place | bool | 是否获取place，默认为false |

如果is_place为true，则返回结果均为saved place，否则为其他类型的pin。

### response

Status: 200

	page: @number
	total_pages: @number

	-----

	[
		{
			"type": @string,
			"pin_id": @number,
			"created_at": @string,
			"pin_object": {
				...
			}
		},
		{...},
		{...}
	]

pin_object中为具体的pin内容（同该pin的get pin返回）。

## 获取my pin :white_check_mark:

`GET /pins/users`

### auth

yes

### filters

| Name | Type | Description |
| --- | --- | --- |
| start_time | string(YYYY-MM-DD hh:mm:ss) | 时间范围，默认为1970-01-01 00:00:00 |
| end_time | string(YYYY-MM-DD hh:mm:ss) | 时间范围，默认为当前日期和时间 |
| page | number | 页数，默认为第1页（头30条） |

### response

Status: 200

	page: @number
	total_pages: @number

	-----

	[
		{
			"type": @string,
			"pin_id": @number,
			"created_at": @string,
			"pin_object": {
				...
			}
		},
		{...},
		{...}
	]

其中pin_object中为具体的pin内容（同该pin的get pin返回）。

## 获取user pin :white_check_mark:

`GET /pins/users/:user_id`

该user发布的anonymous为true的pin将不会被获取（自身的pin除外）。

其余同获取my pin。

## 获取pin属性 :white_check_mark:

`GET /pins/:type/:pin_id/attribute`

### auth

yes

### response

Status: 200

	{
		"type": @string,
		"pin_id": @number,
		"likes": @number,
		"saves": @number
		"comments": @number
	}

## 获取pin的评论 :white_check_mark:

`GET /pins/:type/:pin_id/comments`

### auth

yes

### filters

| Name | Type | Description |
| --- | --- | --- |
| start_time | string(YYYY-MM-DD hh:mm:ss) | 时间范围，默认为1970-01-01 00:00:00 |
| end_time | string(YYYY-MM-DD hh:mm:ss) | 时间范围，默认为当前日期和时间 |
| page | number | 页数，默认为第1页（头30条） |

### response

Status: 200

	page: @number
	total_pages: @number

	-----

	[
		{
			"pin_comment_id": @number,
			"user_id": @number, 如果非自身创建的pin且anonymous为true，则user_id为null
			"anonymous": @boolean,
			"nick_name": @string,
			"content": @string,
			"created_at": @string,
			"vote_up_count": @number,
			"vote_down_count": @number,
			"pin_comment_operations": {
				"vote": @string(up/down/null),
				"vote_timestamp": @string
			}
		},
		{...},
		{...}
	]

## 对于comment的vote :white_check_mark:

`POST /pins/comments/:pin_comment_id/vote`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| vote | string(up,down) | 投票状态 |

如果已经up vote过再up vote则无效，down与之相同。但如果已经up vote了，此时直接down vote是合法的，反之亦然。

### response

Status: 201

## cancel对于comment的vote :white_check_mark:

`DELETE /pins/comments/:pin_comment_id/vote`

### auth

yes

### response

Status: 204

## 获取用户自身pin的相关统计 :white_check_mark:

`GET /pins/statistics`

### auth

yes

### response

Status: 200

	{
		"user_id": @number,
		"count": {
			"created_comment_pin": @number,
			"created_media_pin": @number,
			"created_location": @number,
			"saved_comment_pin": @number,
			"saved_media_pin": @number,
			"saved_place_pin": @number,
		}
	}



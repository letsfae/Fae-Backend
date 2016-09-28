# 用户对于pin的操作

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

其中type可为`media`、`comment`。

### auth

yes

### response

Status: 201

## Unsave :white_check_mark:

`DELETE /pins/:type/:pin_id/save`

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

| Name | Description |
| --- | --- |
| content | 评论内容 |

### response

Status: 201

	{
		"pin_comment_id": @number
	}

## Uncomment :white_check_mark:

`DELETE /pins/comments/:pin_comment_id`

其中type可为`media`、`comment`。

### auth

yes

### response

Status: 204

## 获取saved pin :white_check_mark:

`GET /pins/saved`

其中type可为`media`、`comment`。

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
			"created_at": @string
		},
		{...},
		{...}
	]

## 获取my pin

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
			"created_at": @string
		},
		{...},
		{...}
	]

## 获取user pin

`GET /pins/users/:user_id`

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
			"user_id": @number,
			"content": @string,
			"created_at": @string
		},
		{...},
		{...}
	]

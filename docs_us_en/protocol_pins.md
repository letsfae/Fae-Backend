# 用户对于pin的操作

## Like 点赞

`POST /pins/:type/:pin_id/like`

其中type可为`media`、`comment`。

### auth

yes

### response

Status: 201

## Unlike （注意不是dislike）

`DELETE /pins/:type/:pin_id/like`

其中type可为`media`、`comment`。

### auth

yes

### response

Status: 204

## Save 保存

`POST /pins/:type/:pin_id/save`

其中type可为`media`、`comment`。

### auth

yes

### response

Status: 201

## Unsave

`DELETE /pins/:type/:pin_id/save`

其中type可为`media`、`comment`。

### auth

yes

### response

Status: 204

## comment 评论

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

## Uncomment

`DELETE /pins/:type/:pin_id/comments/:pin_comment_id`

其中type可为`media`、`comment`。

### auth

yes

### response

Status: 204

## 获取saved pin

`POST /pins/saved`

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

`POST /pins/users`

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

`POST /pins/users/:user_id`

其余同获取my pin。

## 获取pin属性

`POST /pins/:type/:pin_id/attribute`

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

## 获取pin的评论

`POST /pins/:type/:pin_id/comments`

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
			"user_id": @number,
			"content": @string,
			"created_at": @string
		},
		{...},
		{...}
	]

# 用户对于pin的操作

## 标记已读 :white_check_mark:

`POST /pins/:type/:pin_id/read`

其中type可为`media`、`comment`、`place`、`location`。

### auth

yes

### response

Status: 201

| Error Code | Description |
| --- | --- |
| 400-3 | 输入ID非数字 |
| 400-7 | 输入type错误 |
| 404-13 | PIN不存在 |

## Like 点赞 :white_check_mark:

`POST /pins/:type/:pin_id/like`

其中type可为`media`、`comment`、`place`、`location`。

### auth

yes

### response

Status: 201

| Error Code | Description |
| --- | --- |
| 400-3 | 输入ID非数字 |
| 400-7 | 输入type错误 |
| 404-13 | PIN不存在 |
| 400-8 | 已Like |
| 403-3 | 距离太远，禁止操作 |
| 404-7 | Location(坐标)不存在 |

## Unlike （注意不是dislike） :white_check_mark:

`DELETE /pins/:type/:pin_id/like`

其中type可为`media`、`comment`、`place`、`location`。

### auth

yes

### response

Status: 204

| Error Code | Description |
| --- | --- |
| 400-3 | 输入ID非数字 |
| 400-7 | 输入type错误 |
| 404-13 | PIN不存在 |
| 400-11 | 未like |

## 创建memo

`POST /pins/:type/:pin_id/memo`

其中type可为`media`、`comment`、`place`、`location`。

此接口可重复调用，之前的memo会覆盖。

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| content | text | memo内容 |

### response

Status: 201

| Error Code | Description |
| --- | --- |
| 404-13 | PIN不存在 |

## 删除memo

`DELETE /pins/:type/:pin_id/memo`

其中type可为`media`、`comment`、`place`、`location`。

### auth

yes

### response

Status: 204

| Error Code | Description |
| --- | --- |
| 404-13 | PIN不存在 |

## Feeling :white_check_mark:

`POST /pins/:type/:pin_id/feeling`

其中type可为`media`、`comment`、`place`、`location`。

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| feeling | integer(0-10) | feeling表情 |

### response

Status: 201

| Error Code | Description |
| --- | --- |
| 400-3 | 输入ID非数字 |
| 400-7 | 输入type错误 |
| 404-13 | PIN不存在 |

## Remove Feeling :white_check_mark:

`DELETE /pins/:type/:pin_id/feeling`

其中type可为`media`、`comment`、`place`、`location`。

### auth

yes

### response

Status: 204

| Error Code | Description |
| --- | --- |
| 400-3 | 输入ID非数字 |
| 400-7 | 输入type错误 |
| 404-13 | PIN不存在 |
| 400-15 | 未发过表情 |

## comment 评论 :white_check_mark:

`POST /pins/:type/:pin_id/comments`

其中type可为`media`、`comment`、`place`、`location`。

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

| Error Code | Description |
| --- | --- |
| 400-3 | 输入ID非数字 |
| 400-7 | 输入type错误 |
| 404-13 | PIN不存在 |
| 403-3 | 距离太远，禁止操作 |
| 404-7 | Location(坐标)不存在 |

## update comment 更新评论

`POST /pins/comments/:pin_comment_id`

### auth

yes

### parameters

同发布pin comment，但所有参数均为可选。

### response

Status: 201

| Error Code | Description |
| --- | --- |
| 400-3 | 输入ID非数字 |
| 404-9 | Comment不存在 |
| 403-2 | 用户不是PIN创建人，无权操作修改 |

## Uncomment :white_check_mark:

`DELETE /pins/comments/:pin_comment_id`

其中type可为`media`、`comment`、`place`、`location`。

### auth

yes

### response

Status: 204

| Error Code | Description |
| --- | --- |
| 400-3 | 输入ID非数字 |
| 404-9 | Comment不存在 |
| 403-2 | 用户不是PIN创建人，无权操作修改 |
| 404-13 | PIN不存在 |

## 获取saved pin

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

| Error Code | Description |
| --- | --- |
| 400-3 | 输入ID非数字 |
| 404-3 | User不存在 |

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

| Error Code | Description |
| --- | --- |
| 400-3 | 输入ID非数字 |
| 400-7 | 输入type错误 |
| 404-13 | PIN不存在 |

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

| Error Code | Description |
| --- | --- |
| 400-3 | 输入ID非数字 |
| 404-9 | Comment不存在 |
| 400-12 | 已vote up |
| 400-13 | 已vote down |

## cancel对于comment的vote :white_check_mark:

`DELETE /pins/comments/:pin_comment_id/vote`

### auth

yes

### response

Status: 204

| Error Code | Description |
| --- | --- |
| 400-3 | 输入ID非数字 |
| 404-9 | Comment不存在 |
| 400-14 | 未vote |

## 获取用户自身pin的相关统计 :white_check_mark:

`GET /pins/statistics`

### auth

yes

### response

Status: 200

	{
		"user_id": @number,
		"count": {
			"created_location_pin": @number,
			"saved_place_pin": @number,
		}
	}


## 创建collection

`POST /collections`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| name | string | 名字 |
| description | string | 描述 |
| type | string('location','place') | 类型 |
| is_private | boolean | 是否私有 |

### response

Status: 201

	{
		"collection_id": @number
	}

## 获取所有collection

`GET /collections`

### auth

yes

### response

Status: 200

	[
		{}, object的内容同获取一个collection（但不包含`pin_id`）
		...
	]

## 更新collection

`POST /collections/:collection_id`

type不可修改，其余字段同创建collection（必须至少存在一个字段）。

| Error Code | Description |
| --- | --- |
| 403-7 | 用户不是collection创建者，无权操作修改 |

## 删除collection

`DELETE /collections/:collection_id`

### auth

yes

### response

Status: 204

| Error Code | Description |
| --- | --- |
| 403-7 | 用户不是collection创建者，无权操作修改 |

## 获取一个collection

`GET /collections/:collection_id`

### auth

yes

### response

Status: 200

	{
		"collection_id": @number,
		"name": @string,
		"user_id": @number,
		"description": @string,
		"type": @string,
		"is_private": @bool,
		"created_at": @string,
		"last_updated_at": @string, update包括对于collection本身的更新以及对于pin的添加与删除
		"count": @number, pin的数量，因为在获取collection总表的时候不会返回具体的pin id，因此需要后端记录该数量
		"pins": [
			{
				"pin_id": @number,
				"added_at": @string 加入collection的时间
			}
			...
		]
	}

| Error Code | Description |
| --- | --- |
| 403-7 | 用户不是collection创建者，无权操作修改 |

## 收藏pin到collection

`POST /collections/:collection_id/save/:type/:pin_id`

### auth

yes

### response

Status: 201

| Error Code | Description |
| --- | --- |
| 403-7 | 用户不是collection创建者，无权操作修改 |
| 400-7 | 输入type错误 |

## 从collection删除一个pin的收藏

`DELETE /collections/:collection_id/save/:type/:pin_id`

### auth

yes

### response

Status: 204

| Error Code | Description |
| --- | --- |
| 403-7 | 用户不是collection创建者，无权操作修改 |
| 400-7 | 输入type错误 |

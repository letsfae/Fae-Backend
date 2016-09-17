# 地图及各类Pin接口

分页数据返回在response header中。

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
| type (optional) | string(user,comment,media,faevor) | 筛选类型，默认为所有，类型之间用逗号隔开 |
| max_count (optional) | number | 返回节点最大数量，默认为30，最大为100 |

对于一直在更新的user点，可以每隔一段时间获取一次。

当获取多种类型节点时，节点返回数量和节点类型顺序及max_count有关（如：第一种节点数量为N，则第二种节点数量最多返回`max_count - N`，如果`N >= max_count`，则没有第二种节点返回）。

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

## 创建新tag :white_check_mark:

`POST /tags`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| title | string | tag名字，只能包含大小写字母数字和下划线 |
| color (optional) | string(#xxxxxx) | 颜色，默认无颜色 |

### response

Status: 201

	{
		"tag_id": @number
	}

如果tag已经创建，则会直接返回`tag_id`。

## 获取tag :white_check_mark:

`GET /tags`

### auth

yes

### filters

| Name | Type | Description |
| --- | --- | --- |
| page (optional) | number | 页数，默认为第1页（头30条） |

如不设定type，则按照tag热度（即引用次数）降序返回。

### response

Status: 200

	page: @number
	total_pages: @number

	-----

	[
		{
			"tag_id": @number,
			"title": @string,
			"color": @string
		},
		{...},
		{...}
	]

## 获取指定tag :white_check_mark:

`GET /tags/:tag_id`

### auth

yes

### response

Status: 200

	{
		"tag_id": @number,
		"title": @string,
		"color": @string
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
| file_ids | file_id | 最多5个，通过;区分 |
| tag_ids(optional) | tag_id | 最多50个，通过;区分 |
| description | string | 描述 |
| geo_latitude | number | 纬度 |
| geo_longitude | number | 经度 |

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
		"user_id": @number
		"file_ids": [
			@number, 
			..., 
			@number
		],
		"tags_ids": [
			@number, 
			..., 
			@number
		],
		"description": @string,
		"geolocation": {
			"latitude": @number,
			"longitude": @number
		},
		"created_at": @string
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

## 发布faevor :white_check_mark:

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
| description | string | 描述 |
| due_time | string(YYYY-MM-DD hh:mm:ss) | 终止时间 |
| expire_time | string(YYYY-MM-DD hh:mm:ss) | 过期时间 |
| geo_latitude | number | 纬度 |
| geo_longitude | number | 经度 |

### response

Status: 201

	{
		"faevor_id": @number
	}

## 更新faevor :white_check_mark:

`POST /faevors/:faevor_id`

### auth

yes

### parameters

同发布faevor，但所有参数均为可选。

如果需要删除file_ids、tags_id、bouns，将字段内容置位`null`。

### response

Status: 201

## 获取faevor :white_check_mark:

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
		"tags_ids": [
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

## 获取某个用户的所有faevor :white_check_mark:

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

## 删除faevor :white_check_mark:

`DELETE /faevors/:faevor_id`

### auth

yes

### response

Status: 204

# 地图及各类Pin接口

分页数据返回在response header中。

interaction_radius:

- duration为pin的活跃时间，过活跃时间之后不会在map中显示，但是可以在mapboard中查到。后端通过get map的`in_duration`参数实现。
- interaction_radius，该范围内用户才可以参与pin的交互。交互的定义为：like, comment, vote, reply。

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

取地图数据 :white_check_mark:

## `GET /map`

### auth

yes

### filters

| Name | Type | Description |
| --- | --- | --- |
| geo_latitude | number | 中心点纬度 |
| geo_longitude | number | 中心点经度 |
| radius (optional) | number | 半径，默认值为200m |
| type | string(user or comment,media,chat_room) | 筛选类型，类型之间用逗号隔开 |
| max_count (optional) | number | 返回节点最大数量，默认为30，最大为100 |
| in_duration (optional) | boolean | 只显示在活跃时间内的pin，默认为false |
| user_updated_in (optional) | number | 显示多久时间内更新过坐标的用户（仅针对user有效），单位min，默认不限制 |
| is_saved (optional) | bool | 默认false |
| is_unsaved (optional) | bool | 默认false |
| is_liked (optional) | bool | 默认false |
| is_unliked (optional) | bool | 默认false |
| is_read (optional) | bool | 默认false |
| is_unread (optional) | bool | 默认false |

对于一直在更新的user点，可以每隔一段时间获取一次。

user类型节点只能单独获取。其他类型节点可同时获取（根据Pin创建时间降序排序）。

如果app退出到桌面，ios端将无法发送坐标，`user_updated_in`用于过滤超出活跃时间的但在线的用户。

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

对于user类型的点，考虑到用户隐私问题，服务器会返回5个一定范围内（200m）的随机点, 格式如下：

	{
		"type": "user",
		"user_id": @number,
		"mini_avatar": @number,
		"location_updated_at": @string,
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

## 获取指定tag的pin

`GET /tags/:tag_id/pin`

### auth

yes

### filters

| Name | Type | Description |
| --- | --- | --- |
| page (optional) | number | 页数，默认为第1页（头30条） |

### response

Status: 200

	page: @number
	total_pages: @number

	-----

	[
		{
			"pin_id": @number,
			"type": @number
		},
		{...},
		{...}
	]

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
		"user_pin_operations": {
			"is_read": @boolean, 对当前用户是否已读
			"read_timestamp" @string,
			"is_liked": @boolean, 对当前用户是否点赞
			"liked_timestamp" @string,
			"is_saved": @boolean 对当前用户是否收藏
			"saved_timestamp" @string,
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
| description | string | 描述 |
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
		"anonymous": @boolean,
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
		"created_at": @string,
		"liked_count": @number,
		"saved_count": @number,
		"comment_count": @number,
		"user_pin_operations": {
			"is_read": @boolean, 对当前用户是否已读
			"read_timestamp" @string,
			"is_liked": @boolean, 对当前用户是否点赞
			"liked_timestamp" @string,
			"is_saved": @boolean 对当前用户是否收藏
			"saved_timestamp" @string,
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

## 更新faevor (待定)

`POST /faevors/:faevor_id`

### auth

yes

### parameters

同发布faevor，但所有参数均为可选。

如果需要删除file_ids、tags_id、bouns，将字段内容置位`null`。

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

----------

部分ChatRoom接口（与聊天相关）在chats相关协议文件中。

----------

## 创建ChatRoom :white_check_mark:

`POST /chat_rooms`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| title | string(100) | 聊天室名 |
| geo_latitude | number | 纬度 |
| geo_longitude | number | 经度 |
| duration | number | 持续显示时间，前端需默认为1440,单位为min |
| interaction_radius (optional) | number | 交互范围，默认不存在，单位m |
| description (optional) | string | 描述 |
| tag_ids (optional) | tag_id | 最多50个，通过;区分 |
| capacity (optional) | number | 房间容量，默认50，范围5-100 |

### response

Status: 201

	{
		"chat_room_id": @number
	}

## 更新ChatRoom :white_check_mark:

`POST /chat_rooms/:chat_room_id`

### auth

yes

### parameters

同创建ChatRoom，但所有参数均为可选。

### response

Status: 201

## 获取ChatRoom :white_check_mark:

`GET /chat_rooms/:chat_room_id`

### auth

yes

### response

Status: 200

	{
		"chat_room_id": @number,
		"title": @string,
		"user_id": @number 创建者id
		"geolocation": {
			"latitude": @number,
			"longitude": @number
		},
		"last_message": @string,
		"last_message_sender_id": @number,
		"last_message_type": @string,
		"last_message_timestamp": @string,
		"created_at": @string,
		"capacity": @number,
		"tags_ids": [
			@number, 
			..., 
			@number
		],
		"description": @string
	}

## 获取某个用户创建的所有ChatRoom :white_check_mark:

`GET /chat_rooms/users/:user_id`

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

具体数组内对象同“创建ChatRoom”所得到的对象。

## 删除ChatRoom

禁止删除ChatRoom。

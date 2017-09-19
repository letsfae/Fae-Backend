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
| type | string(user or place or location or comment,media,chat_room) | 筛选类型，类型之间用逗号隔开 |
| max_count (optional) | number | 返回节点最大数量，默认为30，最大为100 |
| in_duration (optional) | boolean | 只显示在活跃时间内的pin，默认为false（对user,place无效） |
| user_updated_in (optional) | number | 显示多久时间内更新过坐标的用户（仅针对user有效），单位min，默认不限制 |
| is_saved (optional) | bool | 默认该字段不设置（无限制），可设置为true/false |
| is_unsaved (optional) | bool | 同上 |
| is_liked (optional) | bool | 同上 |
| is_unliked (optional) | bool | 同上 |
| is_read (optional) | bool | 同上 |
| is_unread (optional) | bool | 同上 |
| categories (optional) | string | 用`;`分隔的class2的名字，仅针对place有效 |

对于一直在更新的user点，可以每隔一段时间获取一次。

user、place、location类型节点只能单独获取。其他类型节点可同时获取（根据Pin创建时间降序排序）。

如果app退出到桌面，ios端将无法发送坐标，`user_updated_in`用于过滤超出活跃时间的但在线的用户。

如果is_read为false，则is_saved/is_unsaved和is_liked/is_unliked无效。

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
		"user_name": @string (if show_user_name is true, else null),
		"user_nick_name": @string,
		"user_age": @number (if show_age is true, else null),
		"user_gender": @string (if show_gender is true, else null),
		"mini_avatar": @number,
		"location_updated_at": @string,
		"short_intro": @string,
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

## 获取指定tag的pin :white_check_mark:

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
		"user_id": @number 创建者id,
		"nick_name": @string,
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
		"tag_ids": [
			@number, 
			..., 
			@number
		],
		"description": @string,
		"members": [ 群聊用户id
			@number,
			...
		]
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

## 获取地点数据 :white_check_mark:

`GET /places/:place_id`

### auth

yes

### response

Status: 200

	{
		"place_id": @number,
		"name": @string,
		"categories": {
			"class1": @string,
			"class1_icon_id": @number,
			"class2": @string,
			"class2_icon_id": @number,
			"class3": @string,
			"class3_icon_id": @number,
			"class4": @string,
			"class4_icon_id": @number
		},
		"geolocation": {
			"latitude": @number,
			"longitude": @number
		},
		"location": {
			"city": @string,
	        "country": @string,
	        "state": @string,
	        "address": @string,
	        "zip_code": @string
		},
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
			"memo": @string, 用户对当前pin的memo 
			"feeling": @number,
			"feeling_timestamp": @string,
		}
	}

## 发布location

`POST /locations`

注： location仅个人可见。

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
		"location_id": @number
	}

## 更新location

`POST /locations/:location_id`

### auth

yes

### parameters

同发布location，但所有参数均为可选。

### response

Status: 201

## 获取location		

`GET /locations/:location_id`

### auth

yes

### response

Status: 200

	{
		"location_id": @number,
		"content": @string,
		"geolocation": {
			"latitude": @number,
			"longitude": @number
		},
		"created_at": @string,
	}

## 获取该用户的所有location

`GET /locations`

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

具体数组内对象同“获取location”所得到的对象。

## 删除location

`DELETE /locations/:location_id`

### auth

yes

### response

Status: 204
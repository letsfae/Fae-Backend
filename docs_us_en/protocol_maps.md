# Map And Other Pins Interface

Paging data should be returned to the response header. 

## update the current coordination of the user itself :white_check_mark:

`POST /map/user`

Updating once every specified time interval. Only the mobile equipment has the access to update the coordination and other equipment is not permitted. 

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| geo_latitude | number | latitude |
| geo_longitude | number | longitude |

### response

Status: 201

If 422 is returned, the possible reason is that the current equipment is not the mobile equipment. 

## get the map data :white_check_mark:

`GET /map`

### auth

yes

### filters

| Name | Type | Description |
| --- | --- | --- |
| geo_latitude | number | the latitude of the center point |
| geo_longitude | number | the longitude of the center point |
| radius (optional) | number | radius, the default value is 200m |
| type (optional) | string(user,comment,media,faevor,chat_room) | the filter type, the default state is all the filter type are involved and each type is seperated with comma |
| max_count (optional) | number | the maximum amount of the points, the default is 30 and the maximum is 100 |

To the users point that need to be updating all the time, they can be required every period of time. 

When obtained serveral types of ponits, the amount of ponits returned is concerned with the order of the point type and the max_count (If the amount of the points of the first type is N, then the maximum amount of the second type returned is `max_count - N`. If `N >= max_count`, then no points returned of the second type.). 

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

Return an array, every object includes the type, geolocaton and created_at, and other contents are decided by the type (Please refer to related interfaces). 

To the type of user point, the server will return five random points in a specified area because of the user privacy, the format is as follows: 

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

## create new tag :white_check_mark:

`POST /tags`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| title | string | tag name, only the uppercase and lowercase letters and underscores are permitted |
| color (optional) | string(#xxxxxx) | color，the default is no color |

### response

Status: 201

	{
		"tag_id": @number
	}

If the tag has been created, then the `tag_id` will be returned directly. 

## get tag :white_check_mark:

`GET /tags`

### auth

yes

### filters

| Name | Type | Description |
| --- | --- | --- |
| page (optional) | number | page，the default value is the first page (30 pieces from the start) |

If the type is not given, then it returns in descending order according to the popularity of the tag (that is the the number of citations). 

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

## get specified tag :white_check_mark:

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

## post comment :white_check_mark:

`POST /comments`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| content | text | content |
| geo_latitude | number | coordination |
| geo_longitude | number | latitude |

### response

Status: 201

	{
		"comment_id": @number
	}

## update comment :white_check_mark:

`POST /comments/:comment_id`

### auth

yes

### parameters

Same as the post comment, but all the parameters are optional. 

### response

Status: 201

## get comment :white_check_mark:

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

## get all comments of a specified user :white_check_mark:

`GET /comments/users/:user_id`

### auth

yes

### filters

| Name | Type | Description |
| --- | --- | --- |
| start_time | string(YYYY-MM-DD hh:mm:ss) | time range，the default value is 1970-01-01 00:00:00 |
| end_time | string(YYYY-MM-DD hh:mm:ss) | time range, the default value is current date and time |
| page | number | page, the default value is the first page (30 pieces from the start) |

The filter parameters are all the optional. 

### response

Status: 200

	page: @number
	total_pages: @number

	-----

	[
		{...},
		{...}
	]

The object in the specific array is the same as the object got from the "get comment". 

## delete comment :white_check_mark:

`DELETE /comments/:comment_id`

### auth

yes

### response

Status: 204

## post media :white_check_mark:

`POST /medias`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| file_ids | file_id | the maximum is 5, distinguish with semicolon |
| tag_ids(optional) | tag_id | the maximum is 50, distinguish with semicolon |
| description | string | description |
| geo_latitude | number | latitude |
| geo_longitude | number | longitude |

### response

Status: 201

	{
		"media_id": @number
	}

## update media :white_check_mark:

`POST /medias/:media_id`

### auth

yes

### parameters

Same as the post media, but all the parameters are optional. 

At least one file exist, so file_ids which is set to `null` is not allowed. 

If tag_ids needs to be deleted, it should be set to `null`. 

### response

Status: 201

## get media :white_check_mark:

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

## get all media of a specific user :white_check_mark:

`GET /medias/users/:user_id`

### auth

yes

### filters

| Name | Type | Description |
| --- | --- | --- |
| start_time | string(YYYY-MM-DD hh:mm:ss) | time range，the default value is 1970-01-01 00:00:00 |
| end_time | string(YYYY-MM-DD hh:mm:ss) | time range, the default value is current date and time |
| page | number | page, the default value is the first page (30 pieces from the start) |

The filter parameters are all optional. 

### response

Status: 200

	page: @number
	total_pages: @number

	-----

	[
		{...},
		{...}
	]

The object of a specific array is the same as the object got from the "get media". 

## delete media :white_check_mark:

`DELETE /medias/:media_id`

### auth

yes

### response

Status: 204

## post faevor :white_check_mark:

`POST /faevors`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| file_ids(optional) | file_id | the maximum is 5, distinguish with semicolon |
| tag_ids(optional) | tag_id | the maximum is 50, distinguish with semicolon |
| budget | integer | budget，unit is dollar |
| bouns (optional) | string | the description of the bonus |
| name | string | name |
| description | string | description |
| due_time | string(YYYY-MM-DD hh:mm:ss) | due_time |
| expire_time | string(YYYY-MM-DD hh:mm:ss) | expire_time |
| geo_latitude | number | latitude |
| geo_longitude | number | longitude |

### response

Status: 201

	{
		"faevor_id": @number
	}

## update faevor :white_check_mark:

`POST /faevors/:faevor_id`

### auth

yes

### parameters

The same as the post faevor, but all the parameters are the optional. 

If the file_ids, tags_id and bouns need to be deleted, the filed content should be set to `null`. 

### response

Status: 201

## get faevor :white_check_mark:

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

## get all the faevors of a specific users :white_check_mark:

`GET /faevors/users/:user_id`

### auth

yes

### filters

| Name | Type | Description |
| --- | --- | --- |
| start_time | string(YYYY-MM-DD hh:mm:ss) | time range，the default value is 1970-01-01 00:00:00 |
| end_time | string(YYYY-MM-DD hh:mm:ss) | time range, the default value is current date and time |
| page | number | page, the default value is the first page (30 pieces from the start) |

The filter parameters are all optional. 

### response

Status: 200

	page: @number
	total_pages: @number

	-----

	[
		{...},
		{...}
	]

The objece of a specific array is the same as the object got from "get media". 

## delete faevor :white_check_mark:

`DELETE /faevors/:faevor_id`

### auth

yes

### response

Status: 204

----------

Some part of the ChatRoom interface is in the related protocol of the chats files. 

----------

## create ChatRoom

`POST /chat_rooms`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| title | string(100) | chatroom title |
| geo_latitude | number | latitude |
| geo_longitude | number | longitude |

### response

Status: 201

	{
		"chat_room_id": @number
	}

## update ChatRoom

`POST /chat_rooms/:chat_room_id`

### auth

yes

### parameters

The same as the create ChatRoomm, but are the parameters are optional. 

### response

Status: 201

## get ChatRoom

`GET /chat_rooms/:chat_room_id`

### auth

yes

### response

Status: 200

	{
		"chat_room_id": @number,
		"title": @string,
		"user_id": @number creator id
		"geolocation": {
			"latitude": @number,
			"longitude": @number
		},
		"last_message": @string,
		"last_message_sender_id": @number,
		"last_message_type": @string,
		"last_message_timestamp": @string,
		"unread_count": @number
		"created_at": @string
	}

## get all the ChatRooms that was created by a specific user 

`GET /chat_rooms/users/:user_id`

### auth

yes

### filters

| Name | Type | Description |
| --- | --- | --- |
| start_time | string(YYYY-MM-DD hh:mm:ss) | time range，the default value is 1970-01-01 00:00:00 |
| end_time | string(YYYY-MM-DD hh:mm:ss) | time range, the default value is current date and time |
| page | number | page, the default value is the first page (30 pieces from the start) |

The filter parameters are all the optional. 

### response

Status: 200

	page: @number
	total_pages: @number

	-----

	[
		{...},
		{...}
	]

The objece of a specific array is the same as the object got from "create ChatRoom".  

## delete ChatRoom

The delete of ChatRoom is not allowed.

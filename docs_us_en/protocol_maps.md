# Map And Other Pins Interface

Paging data should be returned to the response header. 

interaction_radius:

- duration is the active time of the pin. Nothing showed in the map when over the active time, but can be looked up from the mapboard. The back end team can implement it by `in_duration` of the get map. 
- interaction_radius, only the user in the range can join the interaction of the pin. The defination of the interaction is: like, commnet, vote, reply. 

## update the user current coordination :white_check_mark:

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

## get map :white_check_mark:

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
| in_duration (optional) | boolean | only show the pin in the active time, the default value is false（not valid to user,place） |
| user_updated_in (optional) | number | show time passed since the users updated coordination last time (only valid to the user，unit min，the default is no restriction |
| is_saved (optional) | bool | the default is the field is not set (no restriction)，can be set to true/false |
| is_unsaved (optional) | bool | same as above |
| is_liked (optional) | bool | same as above |
| is_unliked (optional) | bool | same as above |
| is_read (optional) | bool | same as above |
| is_unread (optional) | bool | same as above |
| categories (optional) | string | the name of the class2 which is separate by `;`，only valid to place |

To the users point that need to be updating all the time, they can be required every period of time. 

The type of user、place、location can only be retrived separatly. Other types can be retrived at the same time（sort in descending order accroding to the created time of the pin).

When the app back to the desktop, the ios part can not send the coordination. `user_updated_in` is used to filter the user over the active time but still online.
 
If is_read is false, then is_saved/is_unsaved and is_liked/is_unliked are not valid.

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

To the type of user point, the server will return five random points (200m) in a specified area because of the user privacy, the format is as follows: 

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

## get pin of the specified tag :white_check_mark:

`GET /tags/:tag_id/pin`

### auth

yes

### filters

| Name | Type | Description |
| --- | --- | --- |
| page (optional) | number | page，the default value is the first page (30 pieces from the start) |

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
| duration | number | duration time，the default is 180, unit min |
| interaction_radius (optional) | number | interaction range, the default is null, unit m |
| anonymous (optinal) | boolean | anonymous，the default is false |

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

## get comments :white_check_mark:

`GET /comments/:comment_id`

### auth

yes

### response

Status: 200

	{
		"comment_id": @number,
		"user_id": @number, if the pin is not created by itself and anonymous is true, the user_id is null,
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
			"is_read": @boolean, whether is_read by the current user 
			"read_timestamp": @string,
			"is_liked": @boolean, whether is_liked by the current user 
			"liked_timestamp": @string,
			"is_saved": @boolean whether is_saved by the current user 
			"saved_timestamp": @string,
			"feeling": @number,
			"feeling_timestamp": @string,
		}
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
 
The pin ,posted by the user, which its anonymous is true will not be retrived (except for the self pin).

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
| duration | number | duration time，the default is 180, unit min |
| interaction_radius (optional) | number | interaction range, the default is null, unit m |
| anonymous (optinal) | boolean | anonymous，the default is false |

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
		"user_id": @number,
		"nick_name": @string,
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
		"created_at": @string
		"liked_count": @number,
		"saved_count": @number,
		"comment_count": @number,
		"feelings_count": [
			@number,
			...
		],
		"user_pin_operations": {
			"is_read": @boolean, whether is_read by the current user 
			"read_timestamp": @string,
			"is_liked": @boolean, whether is_liked by the current user 
			"liked_timestamp": @string,
			"is_saved": @boolean whether is_saved by the current user 
			"saved_timestamp": @string,
			"feeling": @number,
			"feeling_timestamp": @string,
		}
	}

## get all medias of a specific user :white_check_mark:

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

The pin ,posted by the user, which its anonymous is true will not be retrived (except for the self pin).

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

## post faevor (pending)

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

## update faevor (pending)

`POST /faevors/:faevor_id`

### auth

yes

### parameters

The same as the post faevor, but all the parameters are the optional. 

If the file_ids, tags_id and bouns need to be deleted, the filed content should be set to `null`. 

### response

Status: 201

## get faevor (pending)

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

## get all the faevors of a specific user :white_check_mark:

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

## delete faevor (pending)

`DELETE /faevors/:faevor_id`

### auth

yes

### response

Status: 204

----------

Some part of the ChatRoom interface is in the related protocol of the chats files. 

----------

## create ChatRoom :white_check_mark:

`POST /chat_rooms`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| title | string(100) | chatroom title |
| geo_latitude | number | latitude |
| geo_longitude | number | longitude |
| duration | number | the duration time，the default value is 1440, unit min |
| interaction_radius (optional) | number | interaction range，the default is null，unit m |
| description (optional) | string | description |
| tag_ids (optional) | tag_id | the max is 50，devided by ; |
| capacity (optional) | number | room capacity, the default value is 50, range 5-100 |

### response

Status: 201

	{
		"chat_room_id": @number
	}

## update ChatRoom :white_check_mark:

`POST /chat_rooms/:chat_room_id`

### auth

yes

### parameters

The same as the create ChatRoomm, but are the parameters are optional. 

### response

Status: 201

## get ChatRoom :white_check_mark:

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

## get all the ChatRooms created by a specific user :white_check_mark:

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

## get place :white_check_mark:

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
		}
	}

## post location

`POST /locations`

attention： location is nonly visible by itself.

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| content | text | content |
| geo_latitude | number | geo_latitude |
| geo_longitude | number | geo_longitude |

### response

Status: 201

	{
		"location_id": @number
	}

## update location

`POST /locations/:location_id`

### auth

yes

### parameters

same as the post location, but all the parameters are optional. 

### response

Status: 201

## get location		

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

## get all the location of the user 

`GET /locations`

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

The objece of a specific array is the same as the object got from "get location".

## delete location

`DELETE /locations/:location_id`

### auth

yes

### response

Status: 204

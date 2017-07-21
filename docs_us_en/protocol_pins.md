# User Operation to the Pin 

## mark read :white_check_mark:

`POST /pins/:type/:pin_id/read`

The type can be `media` or `comment`。

### auth

yes

### response

Status: 201

## like :white_check_mark:

`POST /pins/:type/:pin_id/like`

The type can be `media` or `comment`.

### auth

yes

### response

Status: 201

## unlike （not dislike）:white_check_mark:

`DELETE /pins/:type/:pin_id/like`

The type can be `media` or `comment`.

### auth

yes

### response

Status: 204

## save :white_check_mark:

`POST /pins/:type/:pin_id/save`

The type can be `media`, `comment` or `place`.

### auth

yes

### response

Status: 201

## unsave :white_check_mark:

`DELETE /pins/:type/:pin_id/save`

The type can be `media`, `comment` or `place`.

### auth

yes

### response

Status: 204

## feeling :white_check_mark:

`POST /pins/:type/:pin_id/feeling`

The type can be `media` or `comment`.

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| feeling | integer(0-10) | feeling emotion |

### response

Status: 201

## remove feeling :white_check_mark:

`DELETE /pins/:type/:pin_id/feeling`

The type can be `media` or `comment`.

### auth

yes

### response

Status: 204

## comment :white_check_mark:

`POST /pins/:type/:pin_id/comments`

The type can be `media` or `comment`.

### auth

yes

### parameters

| Name | Description |
| --- | --- |
| content | comment content |
| anonymous (optinal) | boolean | anonymous，default false |

### response

Status: 201

	{
		"pin_comment_id": @number
	}

## update comment  

`POST /pins/comments/:pin_comment_id`

### auth

yes

### parameters

Same as the post pin comment, but all the parameters are optional. 

### response

Status: 201

## uncomment :white_check_mark:

`DELETE /pins/:type/:pin_id/comments/:pin_comment_id`

The type can be `media` or `comment`.

### auth

yes

### response

Status: 204

## get saved pin :white_check_mark:

`Get /pins/saved`

The type could be `media` or `comment`.

### auth

yes

### filters

| Name | Type | Description |
| --- | --- | --- |
| start_time | string(YYYY-MM-DD hh:mm:ss) | time range，the default value is 1970-01-01 00:00:00 |
| end_time | string(YYYY-MM-DD hh:mm:ss) | time range, the default value is current date and time |
| page | number | page, the default value is the first page (30 pieces from the start) |
| is_place | bool | whether to retrive place，default false |

If the is_place is true，then returnt the result with the saved place. If others, return other types of pin. 

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
 
The content of the pin is inside the pin_object (same as the get pin of this pin)

## get my pin :white_check_mark:

`Get /pins/users`

### auth

yes

### filters

| Name | Type | Description |
| --- | --- | --- |
| start_time | string(YYYY-MM-DD hh:mm:ss) | time range，the default value is 1970-01-01 00:00:00 |
| end_time | string(YYYY-MM-DD hh:mm:ss) | time range, the default value is current date and time |
| page | number | page, the default value is the first page (30 pieces from the start) |

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

The content of the pin is inside the pin_object (same as the get pin of this pin)

## get user pin :white_check_mark:

`Get /pins/users/:user_id`

The pin, posted by the user, which the anonymous is true will not be retrived (except for itself pin).
All others are the same as my pin.

## get pin attribute :white_check_mark:

`Get /pins/:type/:pin_id/attribute`

### auth

yes

### response

Status: 200

	{
		"type": @string,
		"pin_id": @number,
		"likes": @number,
		"saves": @number,
		"comments": @number
	}

## get pin comment :white_check_mark:

`Get /pins/:type/:pin_id/comments`

### auth

yes

### filters

| Name | Type | Description |
| --- | --- | --- |
| start_time | string(YYYY-MM-DD hh:mm:ss) | time range，the default value is 1970-01-01 00:00:00 |
| end_time | string(YYYY-MM-DD hh:mm:ss) | time range, the default value is current date and time |
| page | number | page, the default value is the first page (30 pieces from the start) |

### response

Status: 200

	page: @number
	total_pages: @number

	-----

	[
		{
			"pin_comment_id": @number,
			"user_id": @number, If the pin is not created by itself and the anonymous is true, then the user_id is null 
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

## vote of the comment :white_check_mark:

`POST /pins/comments/:pin_comment_id/vote`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| vote | string(up,down) | vote status |

If up vote has been done, then another up vote will be invalid and the same as the down vote. If up vote has been done, then down vote is valid, vice versa. 

### response

Status: 201

## cancel vote of the comment :white_check_mark:

`DELETE /pins/comments/:pin_comment_id/vote`

### auth

yes

### response

Status: 204

## get statistics of related data of itself pin :white_check_mark:

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
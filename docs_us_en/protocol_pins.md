# User Operation to the Pin 

## Like 

`POST /pins/:type/:pin_id/like`

The type could be `media` or `comment`.

### auth

yes

### response

Status: 201

## Unlike （not dislike）

`DELETE /pins/:type/:pin_id/like`

The type could be `media` or `comment`.

### auth

yes

### response

Status: 204

## Save 

`POST /pins/:type/:pin_id/save`

The type could be `media` or `comment`.

### auth

yes

### response

Status: 201

## Unsave

`DELETE /pins/:type/:pin_id/save`

The type could be `media` or `comment`.

### auth

yes

### response

Status: 204

## comment 

`POST /pins/:type/:pin_id/comments`

The type could be `media` or `comment`.

### auth

yes

### parameters

| Name | Description |
| --- | --- |
| content | comment content |

### response

Status: 201

	{
		"pin_comment_id": @number
	}

## Uncomment

`DELETE /pins/:type/:pin_id/comments/:pin_comment_id`

The type could be `media` or `comment`.

### auth

yes

### response

Status: 204

## get saved pin

`POST /pins/saved`

The type could be `media` or `comment`.

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
			"created_at": @string
		},
		{...},
		{...}
	]

## get my pin

`POST /pins/users`

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
			"created_at": @string
		},
		{...},
		{...}
	]

## get user pin

`POST /pins/users/:user_id`

All others are the same as my pin.

## get pin attribute

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

## get pin comment

`POST /pins/:type/:pin_id/comments`

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
			"user_id": @number,
			"content": @string,
			"created_at": @string
		},
		{...},
		{...}
	]

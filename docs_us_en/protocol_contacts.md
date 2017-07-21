# Contacts Interface

The contacts interface includes three relationships, which are friends，follows and blocks.

## post friend request

`POST /friends/request`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| requested_user_id | number | requested user id |
| resend (optional) | boolean | resend friend request, default false |

When the resend is set to true, new friend request will be sent the requested client.

### response

Status: 201

## accept friend request :white_check_mark:

`POST /friends/accept`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| friend_request_id | number | friend request id |

### response

Status: 201

## ignore friend request :white_check_mark:

`POST /friends/ignore`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| friend_request_id | number | friend request id |

### response

Status: 201

## withdraw friend request

`POST /friends/withdraw`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| friend_request_id | number | friend request id |

After withdraw, the other side will not receive the push notification of the withdraw. 

### response

Status: 201

## delete friend :white_check_mark:

`DELETE /friends/:user_id`

### auth

yes

### response

Status: 204

## get all friend request :white_check_mark:

`GET /friends/request`

When the amount of the friend_request got from the sync interface is not 0, requesting this interface to get all the friend requests.

### auth

yes

### response

Status: 200

	[
		{
			"friend_request_id": @number,
			"request_user_id": @number,
			"request_user_name": @string (if show_user_name is true, else null),
			"request_user_nick_name": @string,
			"request_user_age": @number (if show_age is true, else null),
			"request_user_gender": @string (if show_gender is true, else null),
			"request_email": @string,
			"created_at": @string
		},
		...		
	]

The user_name and email are redundant here in order to show easily. 

## get friend list

`GET /friends`

### auth

yes

### response

	[
		{
			"friend_id": @number,
			"friend_user_name": @string (if show_user_name is true, else null),
			"friend_user_nick_name": @string,
			"friend_user_age": @number (if show_age is true, else null),
			"friend_user_gender": @string (if show_gender is true, else null),
		},
		...
	]

## follow someone

`POST /follows`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| followee_id | number | followee id |

### response

Status: 201

## follower of the user (user is followed)

`GET /follows/:user_id/follower`

### auth

yes

### response

	[
		{
			"follower_id": @number,
			"follower_user_name": @string,
			"follower_user_name": @string (if show_user_name is true, else null),
			"follower_user_nick_name": @string,
			"follower_user_age": @number (if show_age is true, else null),
			"follower_user_gender": @string (if show_gender is true, else null),
		},
		...
	]

## followee of the user

`GET /follows/:user_id/followee`

### auth

yes

### response

	[
		{
			"followee_id": @number,
			"followee_user_name": @string,
			"followee_user_name": @string (if show_user_name is true, else null),
			"followee_user_nick_name": @string,
			"followee_user_age": @number (if show_age is true, else null),
			"followee_user_gender": @string (if show_gender is true, else null),
		},
		...
	]

## cancel follow someone 

`DELETE /follows/:followee_id`

### auth

yes

### response

Status: 204

## block someone :white_check_mark:

`POST /blocks`

### auth

yes

### parameters

| Name | Type | Description |
| --- | --- | --- |
| user_id | number | blocked user id |

After calling the interface:

- If they are friends already, then release the friend relationship and can not be the friends forever.
- If they are not friends, then they can not add friends of each other. 

### response

Status: 201

## delete block :white_check_mark:

`DELETE /blocks/:user_id`

### auth

yes

### response

Status: 204
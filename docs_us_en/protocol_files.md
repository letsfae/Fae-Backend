# Files Interface

Unless clearly specified, the type of the parameters is the form-data when uploading. Please pay attention that the Content-Type can not be set. 

#　file cache

Part of the standard of the HTTP protocol is used to the file cache. This part is applied to all the get file data interface. in the response header:  

- `Etag:XXX`：file fingerprint produced by server.
- `Cache-Control: max-age=XXX`：used to designate the cache time. 

After the request from the front end, Etag need to be recorded, and launch the file request to the server after the overtime of the Cashe-Control. `If-None-Match:XXX` field and file fingerprint which content is the Etag need to be carried in the header. If the file is not updated, the server will return `304 Not Modified`, and then the front end will update a chache cycle (continue cache a period time of max-age) , or return the file data. 

# universal file interface 

After uploading the file using the universal file interface, the file will be deleted forever, if it is not called for a period of time (maybe some kind of pin using the file). 

## uploading file :white_check_mark:

`POST /files`

### auth

yes

### parameters

| Name | Description |
| --- | --- |
| file | file content（it uses the type of mine, so the file name and the filename extension are needed. |
| type | string(image,video) |
| description (optional) | semantic file description |
| custom_tag (optional) | labelled file description |

The file size should less than 30MB. 

### response

Status: 201

	{
		"file_id": @number
	}

## get the file content :white_check_mark:

`GET /files/:file_id/data`

### auth

no

### response

Status: 200

Body is the file content.

## get the file attribute :white_check_mark:

`GET /files/:file_id/attribute`

### auth

yes

### response

Status: 200

	{
		"file_id": @number,
		"file_name": @string,
		"created_at": @string,
		"type": @string,
		"mine_type": @string,
		"description": @string,
		"custom_tag": @string
	}

# specialized file interface

## set self avatar :white_check_mark:

`POST /files/users/avatar`

### auth

yes

### parameters

| Name | Description |
| --- | --- |
| avatar | picture content |

The type of the picture must be jpeg and size should be 500x500px. 

### response

Status: 201

## get self avatar :white_check_mark:

`GET /files/users/avatar`

### auth

yes

### response

Status: 200

Picture data of the body, and the `Content-Type` is `image/jpeg`.

## get thumbnail avatar :white_check_mark:

`GET /files/users/avatar/:size`

size is 0, 1, 2.
0 is original image; 1 is the width is 500px, height adaptive; 2 is 200px, height adaptive.

### auth

yes

### response

Status: 200

## get avatar of other users :white_check_mark:

`GET /files/users/:user_id/avatar`

Others are the same as get self avatar.

## set namecard cover :white_check_mark:

`POST /files/users/name_card_cover`

### auth

yes

### parameters

| Name | Description |
| --- | --- |
| name_card_cover | picture content |

The type of the picture must be jpeg. 

### response

Status: 201

## get the namecard cover :white_check_mark:

`GET /files/users/name_card_cover`

### auth

yes

### response

Status: 200

Picture data of the body, and the `Content-Type` is `image/jpeg`. 

## get namecard cover of other users. :white_check_mark:

`GET /files/users/:user_id/name_card_cover`

Others are the same as the get namecard cover. 

## set namecard photo :white_check_mark:

`POST /files/users/name_card_photo`

### auth

yes

### parameters

| Name | Description |
| --- | --- |
| position | picture position (1-8) |
| photo | picture content |

The updating of the picture is decided by the position of the picture and the back end will not do forward filling of the picture (that is the position two will fill forware when the position one does not exist). 

The size of the picture should less than 4MB. 

### response

Status: 201

## delete namecard photo :white_check_mark:

`DELETE /files/users/name_card_photo/:position`

### auth

yes

### response

Status: 204

## get namecard photo in a specified position :white_check_mark:

`GET /files/users/:user_id/name_card_photo/:position`

### auth

no

### response

Status: 200

The picture data of the body. 

## get self namecard photo in a specified position :white_check_mark:

`GET /files/users/name_card_photo/:position`

### auth

yes

Others are the same as the get namecard photo in a specified position.

## set chat room cover_image

`POST /files/chat_rooms/cover_image`

### auth

yes

### parameters

| Name | Description |
| --- | --- |
| chat_room_id | chat room id |
| cover_image | picture content |

### response

Status: 201

## get chat room cover_image

`GET /files/chat_rooms/:chat_room_id/cover_image`

### auth

no

### response

Status: 200

The picture data of the body. 

## get place image :white_check_mark:

`GET /files/places/:place_id/image`

### auth

no

### response

Status: 200

The picture data of the body. 

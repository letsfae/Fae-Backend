# Files Interface

Unless otherwise specified, the type of the parameters is the form-data when uploading. Please pay attention that the Content-Type can not be setted. 

# Files Universal Interface 

After uploading the file using the universal interface, the file will be deleted forever, if it is not called for a period of time (maybe some kind of pin using the file). 

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

The file size should less than 10MB. 

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

# file specified interface

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

## get avatar of other users :white_check_mark:

`GET /files/users/:user_id/avatar`

Others are the same as get self avatar.

## set the background picture of the NameCard :white_check_mark:

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

## get the background picture of the NameCard :white_check_mark:

`GET /files/users/name_card_cover`

### auth

yes

### response

Status: 200

Picture data of the body, and the `Content-Type` is `image/jpeg`. 

## get the background picture of the other users. :white_check_mark:

`GET /files/users/:user_id/name_card_cover`

Others are the same as the backgoround picture of NameCard. 

## set the picture of the NameCard :white_check_mark:

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

## delete the picture of the NameCard :white_check_mark:

`DELETE /files/users/name_card_photo/:position`

### auth

yes

### response

Status: 204

## get the picture of the NameCard in specified position :white_check_mark:

`GET /files/users/:user_id/name_card_photo/:position`

### auth

no

### response

Status: 200

The picture data of the body. 

## get the picture of the self NameCard in specified position :white_check_mark:

`GET /files/users/name_card_photo/:position`

### auth

yes

Others are the same as the get the picture of the NameCard in specified position. 

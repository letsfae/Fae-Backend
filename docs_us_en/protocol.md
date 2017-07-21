# The Introduction of the API

This API document is used to make the communication between the front end and the back end of the Fae App. The communication protocol is the application layer protocol, which is the HTTP(s).  

Base URL：`https://api.letsfae.com/`

## version number

Acoording to the standard of the rest, the version information should be marked in the head. If there is no version number, the default one is the latest version. (Recommend maintaining the version number manually).

`Accept: application/x.faeapp.v1+json`

The format of the version number is `v1`, `v2`, `v3`... only the update of the major，minor should be done with self maintenance in the old version. 

## encoding

- The encoding method of request and response is utf-8.
- The response format of all the response is json. 
- If the body format of the request is json, it will be marked especially. If it is not, the format will be x-www-form-urlencoded(`Content-Type: application/x-www-form-urlencoded`).

## parameters and filters 

The implement of the parameter is according to the request body and get. See more infomation from the API function. 

- The filters of the GET are in the url, such as `/xxxxx?param1=AAA&param2=BBB`. Notice the encoding of the url should be urlencode. 
- The contents of parameters of POST/PUT/DELETE are in the head. 

For all the parameters, if any of them is optional, then it could be inexistent (with default value); If the key is set, then the value must be given (except for some special cases). 

## status code

If the API is called, it will return 2xx when it is successful. If it has error, the error message will be in the error field. 

- 200 OK [GET].
- 201 Created [POST/PUT/PATCH].
- 202 Accepted [*].
- 204 No Content [DELETE]. 
- 400 Invalid Request [POST/PUT/PATCH]. 
- 401 Unauthorized [*]. 
- 403 Forbidden [*]. 
- 404 Not Found [*]. 
- 406 Not Acceptable [GET]. 
- 410 Gone [GET]. 
- 422 Unprocesable Entity [POST/PUT/PATCH]. 
- 500 Internal Server Error [*]. 

## user authentication 

Returning the user_id and token when the login is successful. 

All the request that need to do the user authentication should have auth header. The constuction of  auth header of Fae is as follows:

`Authorization: FAE base64(user_id:token:session_id)`

## open interface

There is no third-party client so far, so the disscusion of this situation is not necessary right now. 

To the client of Fae: 

- In header, the device name need to be marked in the `User-Agent` (e.g. iphone4, iphone6s, nexus6...).
- In header, the `Fae-Client-Version` is the client version (e.g. ios-0.0.1).

## error response

If there is any error, the http header status code will be 4XX or 5XX. There will be json response in the body as follows. (Specified errors will be written in the errors field). 

```
    {                          
       "status_code": @number, 
        "message": @string,
        "error_code": @string
    }
```

For the detailed protocols, please refer to [Error Code](protocol_error_code.md).

# API function 

- [User and Authentication](protocol_users.md)
- [Map and Other Pins](protocol_maps.md)
- [Users' operation to the pin](protocol_pins.md)
- [Contacts](protocol_contacts.md)
- [Chats](protocol_chats.md)
- [Files](protocol_files.md)
- [Richtext](protocol_richtext.md)
- [Pushback](protocol_pushback.md)
- [Others](protocol_others.md)

## synchronization message :white_check_mark:

`GET /sync`

Used to get the amount of synchronization messages (that is whether there are new messages). Used to decide whether the connection has been made (decide whether the user is in the status of login when opening the app again). 

### auth

yes

### response

Status: 200

    {
        "friend_request": @number the amount of friend_request messages,
        "chat": @number the amount of unread messages 
    }

# push back interface 

When related event happens, the server will push back the message actively (only push to the device with the `is_mobile` is true). If the client does not register for the device_id, then it can not receive the push back messages, but the unread messages can be looked up actively according to the sync interface (or call other interface and decide the current status acoording to the error response). 

All the format of the push back messages is json, and the word of type will be used to mark the specified push back type. 

## authentication other device :white_check_mark:

    {
        "type": "authentication_other_device",
        "device_id": @number the device id of another device (this field could be empty),
        "fae_client_version": @string the client version of another device,
        "auth": @boolean If it is true, then the login status is valid for the users, or it means that the user has logged out (the auth is not valid at this time). 
    }

## friends new request

    {
        "type": "friends_new_request",
        "request_user_id": @number request user
    }

## friends request response

    {
        "type": "friends_request_reponse",
        "requested_user_id": @number requested user,
        "result": @string("accept","ignore")
    }

## new message :white_check_mark:

    {
        "type": "chat_new_message",
        "chat_id": @number,
        "last_message": @string,
        "last_message_sender_id": @number,
        "last_message_timestamp": @string,
        "last_message_type": @string,
        "unread_count": @number
    }

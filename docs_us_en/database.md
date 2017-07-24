# Database Structure

## users
- id autoincrement PK
- user_name varchar(30) unique NULL start with continuous letters and no blank.
- email varchar(50) not case sensitivity
- email_verified boolean
- password varchar(70)
- first_name varchar(20)
- last_name varchar(20)
- gender enum(male,female)
- birthday dateTime
- created_at timestamp
- last_login_at timestamp (default null)
- role integer
- login_count integer
- mini_avatar integer (default 0)
- phone string 
- phone_verified boolean

## user_exts
- user_id FK
- status integer
- message text
- show_user_name default true
- show_email
- show_phone
- show_gender
- show_birthday

## verifications
- id PK
- object string(50) specific content
- type enum(phone, email, reset_login)
- code string(20)
- created_at

## sessions
- id PK
- user_id FK [index]
- token varchar(50)
- device_id varchar(50)
- client_version varchar(50)
- geolocation
- is_mobile boolean
- location_updated_at
- created_at

## chats
- id PK
- user_a_id FK
- user_b_id FK
- last_message text
- last_message_timestamp
- last_message_sender_id FK
- last_message_type enum(text,image,sticker,location,audio,customize)
- user_a_unread_count integer default 0
- user_b_unread_count integer default 0
- created_at

attention: user_a_id < user_b_id

## chats in firebase
- id
- alias_id `user_a_id` + `_` + `user_b_id`
- user_a_id FK
- user_b_id FK
- message text
- message_timestamp
- message_sender_id
- message_type
- user_a_unread_count integer default 0
- user_b_unread_count integer default 0
- created_at

## name_cards
- user_id FK same as user_ext table foreign and primary key
- nick_name string(50)
- short_intro text
- tag_ids text (at most 3 tag_ids, separate by ;)
- created_at
- show_gender boolean(false)
- show_age boolean(false)

## name_card_tags
- id PK
- title string(20)
- color string(10) #fff000

## name_cards_saved
- id PK
- user_id FK save namecard user
- name_card_user_id FK name of the user

## files universal upload file interface
- id PK
- user_id FK
- description text nullable
- custom_tag string(20) users custimized label nullable
- type enum(image,video)
- mine_type string(30)
- size integer
- hash string(50) file verification
- directory string(256) offset of the file context according to the storage root context
- file_name_storage string(256) storage file name
- file_name string(256) original file name
- reference_count integer reference number
- created_at

## tags universal tag interface
- id PK
- title string(20)  unique
- color string(10) #fff000
- user_id FK user who create the tag
- reference_count integer number
- created_at

## comments
- id PK
- user_id FK
- content_text string
- created_at 
- liked_count
- saved_count
- comment_count
- feeling_count
- geolocation point
- duration integer (unit in min)
- interaction_
-  (unit in km) default 0
- anonymous default false

## medias
- id PK
- user_id FK
- description text
- geolocation
- tag_ids separate by ;
- file_ids separate by ;
- created_at
- liked_count
- saved_count
- comment_count
- feeling_count
- duration integer (unit in min)
- interaction_radius (unit in km) default 0
- anonymous default false

## faevors
- id PK
- user_id FK
- description text
- geolocation
- name string(100)
- budget integer
- bouns string(50)
- expire_time datetime
- due_time datetime
- tag_ids separate by ;
- file_ids separate by ;
- created_at

## locations
- id PK
- user_id FK
- content_text string
- created_at
- geolocation point

## chat_rooms
- id PK
- user_id FK creator
- title string(100)
- geolocation
- last_message text
- last_message_timestamp
- last_message_sender_id FK
- last_message_type enum(text,image)
- user_count
- created_at
- duration integer (unit in min)
- interaction_radius (unit in km) default 0
- tag_ids
- description
- capacity

## chat_room_users
- id PK
- chat_room_id FK
- user_id FK
- unread_count integer default 0
- created_at

## pin_operations
- id PK
- type enum(media,comment)
- pin_id must be the id of pin which the type is enum
- user_id FK
- liked boolean
- liked_timestamp
- saved boolean
- saved_timestamp
- feeling integer
- feeling_timestamp
- interacted boolean default false

## pin_comments
- id PK
- type enum(media,comment)
- pin_id must be the id of pin which the type is enum
- user_id FK
- anonymous default false
- content text
- created_at
- vote_up_count
- vote_down_count

## pin_comment_operations
- id PK
- pin_comment_id FK
- user_id FK
- vote (1 for up, -1 for down)
- vote_timestamp

## pin_helper
- id PK
- user_id FK creator
- type enum(media,comment,chat_room)
- pin_id
- geolocation
- created_at

## tag_helper
- id PK
- tag_id FK
- pin_id FK
- type enum(media,comment)

## friends
- id PK
- user_id FK reference on users
- friend_id FK reference on users
- created_at

## friend_requests
- id PK
- user_id FK request user reference on users
- requested_user_id FK requested user reference on users
- created_at

## follows
- id PK
- user_id FK
- followee_id FK the id that the user follows

## blocks
- id PK
- user_id FK
- block_id FK blocked user id

## follows
- id PK
- user_id
- followee_id the id that the user follows

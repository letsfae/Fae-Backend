# Database Structure

## users
- id autoincrement PK
- user_name varchar(30) unique NULL 连续的字母开头的无空格的
- email varchar(50) 大小写不敏感的
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

## user_settings
- user_id FK
- email_subscription default true
- show_name_card_options default true
- measurement_units default imperial (imperial / metric)
- shadow_location_system_effect 

## verifications
- id PK
- object string(50) 具体字段
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

注意user_a_id < user_b_id

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
- user_id FK 同user_ext表 外主键
- nick_name string(50)
- short_intro text
- tag_ids text (最多3个tag_id，;分隔)
- created_at
- show_gender boolean(false)
- show_age boolean(false)

## name_card_tags
- id PK
- title string(20)
- color string(10) #fff000

## name_cards_saved
- id PK
- user_id FK 保存name_card的用户
- name_card_user_id FK name所属用户

## files 通用文件类上传接口
- id PK
- user_id FK
- description text nullable
- custom_tag string(20) 用户自定义标签 nullable
- type enum(image,video)
- mine_type string(30)
- size integer
- hash string(50) 文件校验
- directory string(256) 文件目录相对存储根目录的偏移
- file_name_storage string(256) 存储文件名
- file_name string(256) 原始文件名
- reference_count integer 引用计数
- created_at

## tags 通用tag接口
- id PK
- title string(20)  unique
- color string(10) #fff000
- user_id FK 创建该tag的用户
- reference_count integer 引用计数
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
- tag_ids ;分割
- file_ids ;分割
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
- tag_ids ;分割
- file_ids ;分割
- created_at

## locations
- id PK
- user_id FK
- content_text string
- created_at
- geolocation point
- file_id string

## chat_rooms
- id PK
- user_id FK 创建者
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
- type enum(media,comment,place,location)
- pin_id 必须是enum所列举的pin的id
- user_id FK
- liked boolean
- liked_timestamp
- saved array (array of collection_id)
- saved_timestamp
- memo text
- memo_timestamp
- feeling integer
- feeling_timestamp
- interacted boolean default false

## pin_comments
- id PK
- type enum(media,comment)
- pin_id 必须是enum所列举的pin的id
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
- user_id FK 创建者
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
- user_id FK 请求用户 reference on users
- requested_user_id FK 被请求用户 reference on users
- created_at

## follows
- id PK
- user_id FK
- followee_id FK user关注的用户的id

## blocks
- id PK
- user_id FK
- block_id FK 被屏蔽用户id

## follows
- id PK
- user_id
- followee_id 被follow用户的id

## collections
- id PK
- user_id
- created_at
- last_updated_at 注意区分和updated_at的区别。该字段包括对于collection本身的更新以及对于pin的添加与删除
- type enum(location,place,media,comment)
- is_private boolean
- name
- description text
- count

## collection_of_pins
- id PK
- collection_id
- pin_id
- type
- created_at

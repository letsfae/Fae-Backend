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
- role integer
- login_count integer
- mini_avatar integer (default 0)
- phone string 
- phone_verified boolean

## user_exts
- user_id FK
- status integer
- message text

## friendships
- id PK
- user_id FK reference on users
- friend_id FK reference on users
- created_at

## friend_request
- id PK
- user_id FK 请求用户 reference on users
- requested_user_id FK 被请求用户 reference on users
- created_at

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
- created_at

## chats
- id PK
- user_a_id FK
- user_b_id FK
- last_message text
- last_message_timestamp
- last_message_sender_id FK
- last_message_type enum(text,image)
- user_a_unread_count integer default 0
- user_b_unread_count integer default 0
- created_at

注意user_a_id < user_b_id

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

## chat_room_users
- id PK
- chat_room_id FK
- user_id FK
- unread_count integer default 0
- created_at

## pin_operations
- id PK
- type enum(media,comment)
- pin_id 必须是enum所列举的pin的id
- user_id FK
- liked boolean
- liked_timestamp
- saved boolean
- saved_timestamp
- interacted boolean default false

## pin_comments
- id PK
- type enum(media,comment)
- pin_id 必须是enum所列举的pin的id
- user_id FK
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
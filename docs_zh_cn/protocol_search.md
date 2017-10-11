# 搜索类

搜索接口目前为`GET /MAP`接口的增强版本。

所有接口均为只读，考虑到部分HTTP工具对于GET的body调试不友好，因此所有接口均可以使用POST作为GET的替代动词。

为了支持可能出现的复杂参数结构，接口的传入参数将不再采用`form-data`或`x-www-form-urlencoded`，而使用JSON替代之（`Content-Type: application/json`）。

如不做特殊说明，`auth`均为`yes`，`response status code`均为`200`。

## 搜索

该接口可充当自动补全使用。

## request

	GET /search
	{
		"type": "place", // required, currently fixed value
		"content": "keyword", // required
		"location": { // optional, user location, but needs to be set if sort by geo_location
			"latitude": "",
			"longitude": ""
		}
		"size": 10, // optional, defaults to 10
		"offset": 0, // optional, defaults to 0
		"sort": [{"created_at": "desc"}], // optional, defaults to [{"created_at": "desc"}], could be "created_at", "geo_location", "name"; order matters.
		"source": "category_name" // required, could be "category_name", "data"
	}

### response

	[{}, {}] array of place object.

## 批量搜索

如果需要发起批量搜索，可以使用该接口。

## request

	GET /search／bulk
	[
		{search object},
		...
	]

### response

	[[], []] array of search result array.

### example

同时在category_name及data中搜索关键字"abc"。

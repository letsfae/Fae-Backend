# 富文本相关接口

所有文本富文本化后一律采用xml格式向后台传输，如果没有按照正确的格式传输的tag一律按照无意义的文本处理。
富文本格式：

	plaintext.....
	
	<a>@username</a>
	
	plaintext.....
	
	<hashtag>#goodhashtag</hashtag>

	plaintext.....
	
	<image>image_id</image>



在richtext的tag中的没有标签化的满足#文法的，以及没有标签化的@，后台也一律按照无意义文本处理。

所有的更新操作，后台一律将修改前的文本内容删除，重新校验。

## 搜索该hashtag的详细信息

`GET /richtext/hashtag/:hashtag`

返回以该字符串为context的hashtag的具体信息

### auth

yes

### response

Status: 200

	{
		"hashtag_id": @number,
		"context": @string,
		"reference_count": @number
	}

## 搜索所有该内容的hashtag，按照被引用次数高低排名

`GET /richtext/hashtag/:hashtag/contains`

返回以该字符串为部分context的hashtag的具体信息，并取前引用量前三十个包含该字符串的hashtag

### auth

yes

### response

Status: 200

	[
		{
			"hashtag_id": @number,
			"context": @string,
			"reference_count": @number
		},
		{...},
		{...}
	]
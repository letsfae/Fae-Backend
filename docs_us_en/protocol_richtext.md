# Richtext Interface

The xml format is used to transfer all the rich text got from text to the back end. If the tag is not transfered according to the correct format, they will be deal as the meanless text. 
Rich text format:

	plaintext.....
	
	<a>@username</a>
	
	plaintext.....
	
	<hashtag>#goodhashtag</hashtag>

	plaintext.....
	
	<image>image_id</image>


If the tag is not given tag but satisfied with the syntax of # in the richtext, and the @ which is not given tag, the back end will deal with them as the meanless text. 

For all the update operations, the back end will delete all the text content before the modification and verify again.

## get detailed information of the hashtag 

`GET /richtext/hashtag/:hashtag`

Return the detailed information of the hashtag according to the string as the context.

### auth

yes

### response

Status: 200

	{
		"hashtag_id": @number,
		"context": @string,
		"reference_count": @number
	}

## search all hashtags of the content, order by the citaton times 

`GET /richtext/hashtag/:hashtag/contains`

Return the detailed information of the hashtag according to the string as part of the context and get hashtags ranked top 30 that include this string. 

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
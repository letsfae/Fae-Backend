<?php
namespace App\Api\v1\ErrorCodeUtility;

class ErrorCodeUtility
{
	public static const UNLIKE = 1;
    public static const UNCOMMENT = 2;

    //400 Bad Request
    /* 
    	The server cannot or will not process the request due to an apparent client error (e.g., malformed request syntax, too large size, invalid request message framing, or deceptive request routing)
    */

    

    //401 Unauthorized
    /*
    	Similar to 403 Forbidden, but specifically for use when authentication is required and has failed or has not yet been provided. The response must include a WWW-Authenticate header field containing a challenge applicable to the requested resource. See Basic access authentication and Digest access authentication.[33] 401 semantically means "unauthenticated",[34] i.e. the user does not have the necessary credentials.
		Note: Some sites issue HTTP 401 when an IP address is banned from the website (usually the website domain) and that specific address is refused permission to access a website.
    */

    //402 Payment Required
    /*
		Reserved for future use. The original intention was that this code might be used as part of some form of digital cash or micropayment scheme, but that has not happened, and this code is not usually used. Google Developers API uses this status if a particular developer has exceeded the daily limit on requests.
    */

    //403 Access Denied, Forbidden
    /*
		The request was a valid request, but the server is refusing to respond to it. The user might be logged in but does not have the necessary permissions for the resource.
    */

    //404 Not Found
    /*
		The requested resource could not be found but may be available in the future. Subsequent requests by the client are permissible.
    */

    //405 Method Not Allowed
    /*
		A request method is not supported for the requested resource; for example, a GET request on a form which requires data to be presented via POST, or a PUT request on a read-only resource.
    */

    //406 Not Acceptable
    /*
		The requested resource is capable of generating only content not acceptable according to the Accept headers sent in the request.
    */

    //407 Proxy Authentication Required
    /*
		The client must first authenticate itself with the proxy.
    */

    //408 Request Time-out
    /*
		The server timed out waiting for the request. According to HTTP specifications: "The client did not produce a request within the time that the server was prepared to wait. The client MAY repeat the request without modifications at any later time."
    */

    //409 Conflict
    /*
		Indicates that the request could not be processed because of conflict in the request, such as an edit conflict between multiple simultaneous updates.
    */

    //410 Gone
    /*
		Indicates that the resource requested is no longer available and will not be available again. This should be used when a resource has been intentionally removed and the resource should be purged. Upon receiving a 410 status code, the client should not request the resource in the future. Clients such as search engines should remove the resource from their indices.[40] Most use cases do not require clients and search engines to purge the resource, and a "404 Not Found" may be used instead.
    */

	//411 Length Required
	/*
		The request did not specify the length of its content, which is required by the requested resource.
	*/

	//412 Precondition Failed (RFC 7232)
	/*
		The server does not meet one of the preconditions that the requester put on the request.[42]
	*/

	//413 Payload Too Large (RFC 7231)
	/*
		The request is larger than the server is willing or able to process. Previously called "Request Entity Too Large".
	*/

	//414 URI Too Long (RFC 7231)
	/*
		The URI provided was too long for the server to process. Often the result of too much data being encoded as a query-string of 
		a GET request, in which case it should be converted to a POST request.[44] Called "Request-URI Too Long" previously.
	*/
	//415 Unsupported Media Type
	/*
		The request entity has a media type which the server or resource does not support. For example, the client uploads an image 
		as image/svg+xml, but the server requires that images use a different format.
	*/
	//416 Range Not Satisfiable (RFC 7233)
	/*
		The client has asked for a portion of the file (byte serving), but the server cannot supply that portion. For example, 
		if the client asked for a part of the file that lies beyond the end of the file.
	*/
	//417 Expectation Failed
	/*
		The server cannot meet the requirements of the Expect request-header field.
	*/
	//418 I'm a teapot (RFC 2324)
	/*
		This code was defined in 1998 as one of the traditional IETF April Fools' jokes, in RFC 2324, Hyper Text Coffee Pot Control Protocol, and is not expected to be implemented by actual HTTP servers. The RFC specifies this code should be returned by teapots requested to brew coffee.
	*/

	//421 Misdirected Request (RFC 7540)
	/*
		The request was directed at a server that is not able to produce a response (for example because a connection reuse).
	*/

	//422 Unprocessable Entity (WebDAV; RFC 4918)
	/*
		The request was well-formed but was unable to be followed due to semantic errors.
	*/

	//423 Locked (WebDAV; RFC 4918)
	/*
		The resource that is being accessed is locked.
	*/

	//424 Failed Dependency (WebDAV; RFC 4918)
	/*
		The request failed due to failure of a previous request (e.g., a PROPPATCH).
	*/

	//426 Upgrade Required
	/*
		The client should switch to a different protocol such as TLS/1.0, given in the Upgrade header field.
	*/

	//428 Precondition Required (RFC 6585)
	/*
		The origin server requires the request to be conditional. Intended to prevent "the 'lost update' problem, where a client GETs a resource's state, modifies it, and PUTs it back to the server, when meanwhile a third party has modified the state on the server, leading to a conflict."
	*/

	//429 Too Many Requests (RFC 6585)
	/*
		The user has sent too many requests in a given amount of time. Intended for use with rate-limiting schemes.
	*/

	//431 Request Header Fields Too Large (RFC 6585)
	/*
		The server is unwilling to process the request because either an individual header field, or all the header fields collectively, are too large.
	*/

	//451 Unavailable For Legal Reasons
	/*
		A server operator has received a legal demand to deny access to a resource or to a set of resources that includes the requested resource.[54] The code 451 was chosen as a reference to the novel Fahrenheit 451.
	*/
}
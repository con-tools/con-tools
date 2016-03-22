# ConTroll Web Service API

The ConTroll web service API is a REST/JSON API. The API endpoing for the
published public service as http://api.con-troll.org/ and all examples will be
shown with that URL, and using the `curl` command line tool.

## API Conventions And Practices

The REST API accepts certain parameters in the URI, in which case these will be
specified using a colon notation, e.g.: `/operation/:param` where `:param` will
be replaced with the input value passed in the call.

Some calls do not require any input, or only URI input parameters, in which
case either a GET request can be performed or a POST with an empty body. This
will be noted in each API by describing it as "no input needed" or "no request
body needed".

## Authorization

The API supports 4 levels of authorization, and different operations require different
levels of authorization (though some operations support multiple levels).

The authorization levels are:

1. Public access: No authorization is required at all - any client including automated tools
can perform these requests  
2. Convention identity: This is not actually an authorization and is different from "public
access" only in that the caller selects the convention being operated on using the convention's
*public* access key. This type of operation is most common for convention website integrations.  
3. User authorization: A logged in user must authorize this call using a session token.  
4. Convention authorization: A convention automation tool (e.g. backend to backend website
integration) must authorize the call by signing the request with the convention's secret
key. This kind of call also require the "convention identity" level to select the correct
convention.

The documentation will describe the required authorizations for each call. If no required
authorization is mentioned, then the operation is available using "public access".

### Public Access

No additional headers or parameters are required, other than those mentioned in the API 
operation's documentation

### Convention Identify

In order to perform "convention identity" authorization, the caller must retrieve the convention's
public API access key, and submit it as the value of the `Convention` header, or the `convention`
query string parameter.

The API examples (when required) will always use the `Convention` header with the fake convention
API access key `CON123456`.

### User Authorization

In order to perform user authorization, the caller must retrieve a user session token (by performing
a login procedure using the Authentication API calls). Each API call that requires
authorization must contain an `Authorization` header or a `token` query string.

The API examples (when required) will always use the `Authorization` header with
the fake authorization token `ABCD1234`.

### Convention Authorization

In order to perform "convention authorization", the caller must retrieve both the convention's
public API access key as well as the private secret, then create authorization headers as follows:

1. The caller must create a `Convention` header as described under "Convention Identity".
2. The caller must generate a salt value and encode it in to the Base64 character range (the 
actual encoding and the content is not enforced, but it is highly recommended that the salt will be
contain at least 80 bit of cryptographic quality random data).
3. The caller must retrieve the current time from a real time accurate clock (skews of up to 10 minutes
will be tolerated) and format it as an Epoch time stamp (number of seconds since the UNIX epoch).
4. The caller must create a signature by generating a SHA1 checksum of the text that is created from
concatenating the time stamp, a colon, the salt, and the convention secret key and encode the result
as a hexadecimal number (e.g. `HEX(SHA1(time + ":" + salt + secret))`).
5. The caller must create an `Authorization` header with the text `Convention` followed by a space 
and the authorization token text created from the time stamp, salt text and signature separated by color
characters (e.g. `"Convention " + time + ":" + salt + ":" + signature`). 

## API Calls

* [Authentication](api/Authentication.md)
* [Conventions](api/Conventions.md)
* [Managers](api/Managers.md)
* [Tags](api/Tags.md)
* [Events](api/Events.md)

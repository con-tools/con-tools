# ConTroll Web Service API

The ConTroll web service API is a REST/JSON API. The API endpoing for the
published public service as http://api.con-troll.org/ and all examples will be
shown with that URL, and using the `curl` command line tool.

## Conventions

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

### Authentication

ConTroll supports Several modes of authentication:
 * OpenID Connect Providers (e.g. Google+)
 * OAuth version 1 Providers (e.g. Twitter)
 * OAuth version 2 Providers (e.g. Facebook)
 * Classic email and password authentication with a ConTroll managed user database (not recommended), available as "`email-auth`"

#### API

`/auth/verify` : Verify if a user is logged correctly

**Input:**: No input needed
**Output:** A property list containing a boolean property `status` signaling
whether the user is authenticated and logged in properly or not.

*Example:*
```
$ curl http://api.con-troll.org/auth/verify \
  -H 'Authorization: ABCD1234'
```
*Response:*
```
{"status":true}
```

`/auth/list` : Returns a list of available authentication providers

**Input:** No input needed  
**Output:** A list containing provider identifiers. The client is expected to
understand what each identifier means for UI purposes.

*Example:*
```
$ curl http://api.con-troll.org/auth/list
```
*Response:*
```
["google","twitter","facebook","email-auth"]
```

`/auth/start` : Start an authentication session with the requested 3rd party
provider.

**Input:** Property list with the following properties:
* `redirect-url` : URL to redirect the web browser after authentication
  completes.
* `provider` : optionally choose the provider to use for the authentication
  process.If not provided, the default provider (usually "`google`") is used.

**Output:** Property list with the property `auth-url` containing a URL. In
order to complete the authentication process, the client should redirect the
user's browser to the specified URL.

*Example:*
```
$ curl http://api.con-troll.org/auth/start \
  -H 'Content-Type: application/json' \
  -d '{"provider":"facebook","redirect-url":"http://example.com/"}'
```
*Response:*
```
{"auth-url":"https:\/\/www.facebook.com\/v2.4\/dialog\/oauth?client_id=1500534196907805&redirect_uri=http%3A%2F%2Fapi.con-troll.org%2Fauth%2Fcallback&state=yge%2BPmSPTKSUpFqTTF4hZeOVSYZ%2FOZiD&scope=email%2Cpublic_profile&response_type=code&approval_prompt=auto"}
```

*Note:* This API call does not support using the built-in `email-auth` method. For password authentication against the built-in user database, use the `/auth/signin` API call.

`/auth/logout` : Log out the current user.

**Input:** No input needed  
**Output:** Property list with the boolean property `status` signaling the
success of the call.

`/auth/signin` : Perform a login using the built-in password database.

This API call supports both a REST/JSON call semantic as well as a form POST
call semantic.

*REST/JSON API:*

**Input:** Property list with the following properties:
* `email` : The login ID for the user
* `password`: The password for the user

**Output:** Property list with the following properties:
* `status`: boolean value signaling success or failed of the sign in
* `token`: authentication token for the user, if the login succeeded

*Form POST API:*

**Input:**
* `email` : The login ID for the user
* `password`: The password for the user
* `redirect-url`: URL to redirect the browser after sign in is completed.

**Output:** The browser will be redirected back to the `redirect-url` provided, with the following query string fields added:
* `status`: boolean value signaling success or failed of the sign in
* `token`: authentication token for the user, if the login succeeded

*Example:*
```
$ curl http://api.con-troll.org/auth/signin \
  -H 'Content-Type: application/json' \
  -d '{"email":"oded+21@geek.co.il","password":"123456"}'
```
*Response:*
```
{"status":true,"token":"RCFvLHrsjNK8vzQbDVuZAVGrZgo"}
```

`/auth/register` : Register a new user account using the built-in password
database.

This API call supports both a REST/JSON call semantic as well as a form POST
call semantic.

*REST/JSON API:*

**Input:** Property list with the following properties:
* `email`: The login ID for the new user
* `password-register`: The password for the new user
* `name`: The full name of the new user

**Output:** Property list with the following properties:
* `status`: boolean value signaling success or failed of the registration
* `error`: Description of the error if the registration failed

*Form POST API:*

**Input:**
* `email`: The login ID for the new user
* `password-register`: The password for the new user
* `password-confirm`: A confirmation of the password for the new user.
* `redirect-url`: URL to redirect the browser after the registration completed.

**Output:** The browser will be redirected back to the `redirect-url` provided, with the following query string fields added:
* `status`: boolean value signaling success or failed of the sign in
* `token`: authentication token for the user, if the login succeeded

*Example:*
```
$ curl http://api.con-troll.org/auth/register \
  -H 'Content-Type: application/json' \
  -d '{"email":"oded@geek.co.il","password-register":"zxcvbn","name":"Oded Arbel"}'
```
*Response:*
```
{"status":true}
```

`/auth/id` : Retrieve the user identity for the currently logged in user.

**Input:** No input needed  
**Output:** Property list with the following fields:
* `email`: E-Mail address of the user
* `name`: Full name of the user

*Example:*
```
$ curl http://api.con-troll.org/auth/id \
  -H 'Authorization: ABCD1234'
```
*Response:*
```
{"email":"oded@geek.co.il","name":"Oded Arbel"}
```

`/auth/passwordreset` : Ask for a password reset for a user registered in
the built-in password database.

**Input:** Property list with the following fields:
* `email`: login ID of the user for which password reset is requested.
* `redirect-url`: A URL to embed in the password reset email, that the user will
be asked to click on to reset their password. The query string parameter `token`
will be added to the URL and will contain the authorization token that will be
required to use for resetting the password.

**Output:** Property list with the boolean property `status` set to `true`

*Note:* This call always returns status as `true` to prevent a malicious caller
from using this API to check for existence of users.  
*Note:* The client is expected to create a self referencing link that the user
will access after receiving the password reset email. When the user accesses the
password reset URL, the client should then try to verify the token using the
`/auth/verify` call, and if it succeeds - offer the user to choose a new password
and then issue an `/auth/passwordchange` call with the received token as an 
authorization token and the new password.

*Example:*
$ curl http://api.con-troll.org/auth/passwordreset \
  -H 'Content-Type: application/json' \
  -d '{"email":"oded@geek.co.il","redirect-url":"http://controll-client.org/resetpassword"}'
*Response:*
```
{"status":true}
```

`/auth/passwordchange` : Change the password for a user registered in the
built-in password database.

**Input:** Property list with the following fields:
* `password`: New password to set for the user

**Output:** Property list with the boolean property `status` signaliing if
the change was completed successfully.

*Example:*
$ curl http://api.con-troll.org/auth/passwordchange \
  -H 'Content-Type: application/json' \
  -H 'Authorization: ABCD1234' \
  -d '{"password":"123456"}'
*Response:*
```
{"status":true}
```

*Note:* After this call completes successfully, the password reset token is
removed (it is not possible to re-do a password reset) and the user must login
again if they want to do any kind of non-public operation.

### Convention Management

Conventions are managed as a data entity and supports GET (retrieve) and POST (create)

#### API

`POST /entities/conventions` : Create a new convention and client key

This method requires a user authorization and that user becomes the owner for
the convention.

**Input:** Property list with the following fields
* `title`: Name of the new convention.
* `series`: (optional) Name of the convention series.
* `location`: (optional) Convention venue and address.
* `website`: (optional) Convention website URL.
* `slug`: (optional) text descriptor for the convention URLs. Generated from title if not provided.

**Output:** A property list containing the details of the new convention record, with the following
fields:
* `status`: the boolean value true if the creation succeeded.
* `slug`: text descriptor for the convention URLs.
* `id`: numeric identifier for the convention in the system.
* `key`: Client key identifier, for use in website integrations.
* `secret`: Client key secret, for use in website backend integrations.

*Example:*
```
$ curl http://api.con-troll.org/entities/conventions \
  -H 'Authorization: ABCD1234' \
  -H Content-Type:application/json \
  -d '{"title":"My Convention"}'
```
*Response:*
```
{"status":true,"slug":"my-convention","id":15,"key":"asdfjsadf","secret":"lskadjfas"}
```

`GET /entities/conventions/<id>` : Retrieve public convention information


**Input:** Convention numeric ID or slug as the URL parameter   
**Output:** A property list containing the public details of the convention record
fields:
* `title`: text descriptor for the convention URLs.
* `slug`: text descriptor for the convention URLs.
* `id`: numeric identifier for the convention in the system.
* `series`: name of the convention series

*Example:*
```
$ curl http://api.con-troll.org/entities/conventions/1
```
*Response:*
```
{"id":"1","title":"ביגור 16","slug":"ביגור-16","series":"ביגור"}
```

`GET /entities/conventions` : Retrieve a list of all conventions


**Input:** No input needed  
**Output:** An array containing a list of property lists containing the public details of the convention record
fields:
* `title`: text descriptor for the convention URLs.
* `slug`: text descriptor for the convention URLs.
* `id`: numeric identifier for the convention in the system.
* `series`: name of the convention series

*Example:*
```
$ curl http://api.con-troll.org/entities/conventions
```
*Response:*
```
[{"id":"1","title":"ביגור 16","slug":"ביגור-16","series":""},{"id":"2","title":"ביגור 16 פאבקון","slug":"ביגור-16-פאבקון","series":""},{"id":"82","title":"ביגור 16 לארפ פורים","slug":"ביגור-לארפ-פורים","series":""}]
```

### Event Management

Events are managed as a data entity and supports GET (retrieve), POST (create), PUT (update) and DELETE (delete).

Some methods require both user authentication and convention public identification (i.e. select the convention for which
to apply the operation using the convention key), while other require only convention public identification.

#### API

`POST /entities/events` : Create a new event

This method requires a convention identity and a user authorization. The submitting user becomes the event owner and
point of contact.

**Input:** Property list with the following fields  
* `title`: Event title
* `teaser`: Event teaser text
* `description`: Event long description text
* `requires-registration`: Does the event requires people to register, or is it free for all (boolean value)
* `duration`: Event expected duration in minutes
* `min-attendees`: Minimum number of attendees required to start the event
* `max-attendees`: Maximum number of attendees that can be accommodated
* `notes-to-staff`: Note about the event for the management staff
* `logitsical-requirements`: Notes about the events for the logistics team
* `notes-to-attendees`: Notes about the event to show potential attendees
* `scheduling-constraints`: Notes about the availability of the event submitter for scheduling
* `tags`: property list of system tags, each has a key of the system tag title and the value is a
	system tag value or an array of system tag values. The system will not complain if new tags, are
	submitted, instead generating them as default tag specification with requirement of "one" if a
	single value is provided or "one-or-more" if a list is provided
* `data`: Custom data to be stored and retrieved - can be any JSON value

**Output:** A property list containing the details of the new event record, with the following
fields:
* `status`: the boolean value true if the creation succeeded
* `id`: numeric identifier for the event in the system

*Example:*
```
$ curl http://api.con-troll.org/entities/events \
  -H 'Authorization: ABCD1234' \
  -H 'Convention: CON123456' \
  -H Content-Type:application/json \
  -d '{"title": "my event","teaser": "This is a very fancy game",
  "description": "In this very fancy game, we will frolic and have fun","requires-registration": true,
  "duration": 60,"min-attendees": 3,"max-attendees": 6,"notes-to-staff": null,
  "logitsical-requirements": "Brooms, lots and lots of brooms",
  "notes-to-attendees": "Bring your dirt on, and don't forget to have a lot of fun",
  "scheduling-constraints": "I'm like, good, with like, whenever",
  "tags":{"age-requirement":"all ages","event_type": "Role playing game", "taxonomy":["rpg","pen&paper"]},
  "data":{"field":"This is just some custom field"}}'
```
*Response:*
```
{"status":true,"id":123}
```

*Note about "free text tags"*: The system supports free form text text by allowed the convention manager
to create a system tag called "taxonomy" whose requirement specification is "any" (`*`). This method
allows the manager to manage free form taxonomy like any other system tag offering features such as
consolidating almost identical tags, removing confusing tags across the entire convention, etc`.

`GET /entities/events/:id` : Retrieve an existing event by id.

This method requires a convention public identity to retrieve an event that has the status
"approved". To retireve events with other statuses, the call must carry a user authorization
for a user that is a manager for the convention.

**Input:** The id of the event must be specified in the URI  
**Output:** A property list containing the details of the new event record, with the following
fields:
* `id`: numeric identifier for the convention in the system.
* `title`: Event title
* `teaser`: Event teaser text
* `description`: Event long description text
* `requires-registration`: Does the event requires people to register, or is it free for all (boolean value)
* `duration`: Event expected duration in minutes
* `min-attendees`: Minimum number of attendees required to start the event
* `max-attendees`: Maximum number of attendees that can be accommodated
* `notes-to-staff`: Note about the event for the management staff
* `logitsical-requirements`: Notes about the events for the logistics team
* `notes-to-attendees`: Notes about the event to show potential attendees
* `scheduling-constraints`: Notes about the availability of the event submitter for scheduling
* `data`: Custom data to be stored and retrieved - can be any JSON value
* `user`: Name and e-mail address of the owner of the event
* `staff_contact`: Name and email address of the staff contact fot the event (if set)
* `tags`: property list of system tags associated with the event and their values as either a
	single value (if the tag is of the requirement type "one") or a list (if the tag is of the
	requirement type "one-or-more" or "any"). 
* `price`: default registration cost for the event
* `status`: approval status of the event

`GET /entities/events` : Retrieve a list of events for this convention.

This method requires a convention public identity to retrieve the public event list (events that are approved).

If user authorization is provided, the list of events will also include any events submitted by the authorizing
user, regardless of status.

If user authorization is provided and the authorizing user is a manager of the convention, then all events are
returned, regardless of status.

**Input:** No input required  
**Output:** Array of event objects, each presented as a property list with the following fields:
* `id`: numeric identifier for the convention in the system.
* `title`: Event title
* `teaser`: Event teaser text
* `description`: Event long description text
* `requires-registration`: Does the event requires people to register, or is it free for all (boolean value)
* `duration`: Event expected duration in minutes
* `min-attendees`: Minimum number of attendees required to start the event
* `max-attendees`: Maximum number of attendees that can be accommodated
* `notes-to-staff`: Note about the event for the management staff
* `logitsical-requirements`: Notes about the events for the logistics team
* `notes-to-attendees`: Notes about the event to show potential attendees
* `scheduling-constraints`: Notes about the availability of the event submitter for scheduling
* `data`: Custom data to be stored and retrieved - can be any JSON value
* `user`: Name and e-mail address of the owner of the event
* `staff_contact`: Name and email address of the staff contact fot the event (if set) 
* `tags`: property list of system tags associated with the event and their values as either a
	single value (if the tag is of the requirement type "one") or a list (if the tag is of the
	requirement type "one-or-more" or "any"). 
* `price`: default registration cost for the event
* `status`: approval status of the event

`PUT /entities/events/:id` : Update event data.

This method requires a convention public identity as well as user authorization. If the authorizing user is
not a manager in the convention, then they may still update some of the fields of the event - if they are
the owner of the event and the event is still in the initial status of `SUBMITTED`. Otherwise the authorizing
user must be a manager in the convention.

**Input:** A property list including all the fields that should be changed. It is not required to send fields
whose value should not be changed. For the full list of fields, consult the table in the *output* section. In
addition, the special field `remove-tags` is supported and when specified the system will try to remove the
specified tag values. If a tag that has a "one" or "one-or-more" requirement is completely removed (and not
re-added using the `tags` field), the update will fail.  
**Output:** The event details after the update has completed, including the following fields:
* `id`: numeric identifier for the convention in the system.
* `title`: Event title
* `teaser`: Event teaser text
* `description`: Event long description text
* `requires-registration`: Does the event requires people to register, or is it free for all (boolean value)
* `duration`: Event expected duration in minutes
* `event_type`: Event type, as specified by the `event_type` tag type
* `age-requirement`: Event age requirements as specified by the `age_requirement` tag type
* `min-attendees`: Minimum number of attendees required to start the event
* `max-attendees`: Maximum number of attendees that can be accommodated
* `notes-to-staff`: Note about the event for the management staff
* `logitsical-requirements`: Notes about the events for the logistics team
* `notes-to-attendees`: Notes about the event to show potential attendees
* `scheduling-constraints`: Notes about the availability of the event submitter for scheduling
* `data`: Custom data to be stored and retrieved - can be any JSON value
* `user`: Name and e-mail address of the owner of the event
* `staff_contact`: Name and email address of the staff contact fot the event (if set) 
* `tags`: property list of system tags associated with the event and their values as either a
	single value (if the tag is of the requirement type "one") or a list (if the tag is of the
	requirement type "one-or-more" or "any"). 
* `price`: default registration cost for the event
* `status`: approval status of the event

`DELETE /entities/events/:id` : Cancel an event.

This method requires a convention public identity and a user authorization for a user that is a manager
in the convention.

The event is not actually deleted, it is just moved immediately to the `CANCELLED` status where it is not
shown in any public list.

**Input:** The id of the event must be specified in the URI  
**Output:** A property list containing the status of the operation:
* `status`: boolean value set to `true` if the operation was successful.
* `error`: the error message if the operation has failed

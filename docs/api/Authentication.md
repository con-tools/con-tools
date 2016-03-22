[Back to API list](../API.md)

# Authentication

ConTroll supports Several modes of authentication:
 * OpenID Connect Providers (e.g. Google+)
 * OAuth version 1 Providers (e.g. Twitter)
 * OAuth version 2 Providers (e.g. Facebook)
 * Classic email and password authentication with a ConTroll managed user database (not recommended), available as "`email-auth`"

## API

### Verify if a user is logged correctly

`/auth/verify`

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

### Returns a list of available authentication providers

`/auth/list`

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

### Start an authentication session with the requested 3rd party provider.

`/auth/start`

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

### Log out the current user

`/auth/logout`

**Input:** No input needed  
**Output:** Property list with the boolean property `status` signaling the
success of the call.

### Perform a login using the built-in password database

`/auth/signin`

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

### Register a new user account using the built-in password database

`/auth/register`

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

### Retrieve the user identity for the currently logged in user

`/auth/id`

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

### Ask for a password reset for a user registered in the built-in password database

`/auth/passwordreset`

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

### Change the password  for a user registered in the built-in password database

`/auth/passwordchange`

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

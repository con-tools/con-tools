[Back to API list](../API.md)

# Convention Users

This API allows limited manipulation of user objects in the system, specifically listing and
filtering to allow convention managers to select users from a list instead of typing names and emails.

As such this API is currently only available when the authorized user is a convention manager.

## API

### List All Users

`GET /entities/users`

**Input:** No input is required  
**Output:** An array of property lists, each containing the details of a user, with the following fields:
* `id`: The system user id for the user
* `email`: The email address of the user
* `name` : The full name of the user

*Example:*
```
$ curl -X GET "http://api.con-troll.org/entities/users" \
  -H "Content-Type: application/json" \
  -H "Convention: CON123456" \
  -H "Authorization: ABCD1234"
```
*Response:*
```
[ { "id": "4", "email": "oded@geek.co.il", "name": "Oded Arbel" } ]
```

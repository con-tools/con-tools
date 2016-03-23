[Back to API list](../API.md)

# Convention Managers

Convention managers are the authorization given to specific users to manage aspects of the convention, and the
implementation allows other managers to give users the authorization or revoke it from them.

Currently all managers have the same abilities, though a role system is supported and may be used in the future.

All methods in this entity API require convention identity and user authorization of a user that is already a
registered as a manager for the convention.

## API

### List All Managers

`GET /entities/managers`

**Input:** No input is required  
**Output:** An array of property list, each containing the details of a manager, with the following fields:
* `id`: The system user id for the manager
* `email`: The email address of the user
* `name` : The full name of the user

*Example:*
```
$ curl -X GET "http://api.con-troll.org/entities/managers" \
  -H "Content-Type: application/json" \
  -H "Convention: CON123456" \
  -H "Authorization: ABCD1234"
```
*Response:*
```
[ { "id": "4", "email": "admin@con-troll.org", "name": "Admin User" } ]
```

### Add A Manager

`POST /entities/managers`

Add another manager to the convention

**Input:** A property list containing the details of the user to add as a manager. Either of the following
fields are supported:
* `id`: The system ID of the user
* `email`: The email address of the user to add
**Output:** A property list showing the results of the operation with the following fields:
* `status`: A boolean value indicating whether the operation succeeded or not
* `error`: An error message in case the operation did not succeed, describing the problem.

*Note:* It is an error to provide both fields, even if both describe the same user

*Example:*
```
curl -X POST "http://api.con-troll.org/entities/managers" \
  -H "Content-Type: application/json" \
  -H "Convention: CON123456" \
  -H "Authorization: ABCD1234" \
   -d '{"email": "oded@geek.co.il"}' 
```
*Response:*
```
{"status":true,"name":"Oded Arbel","email":"oded@geek.co.il","id":"1"}
```

### Remove A Manager

`DELETE /entities/managers/:id`

Remove a manager from the convention.

**Input:** The system user id of the manager to be removed (as returned by the "add" operation or the "list" operation)  
**Output:** A property list showing the results of the operation with the following fields:
* `status`: A boolean value indicating whether the operation succeeded or not
* `error`: An error message in case the operation did not succeed, describing the problem.

*Note:* The user cannot remove themselves from the convention manager list

*Example:*
```
curl -X DELETE "http://api.con-troll.org/entities/managers/1" \
  -H "Content-Type: application/json" \
  -H "Convention: CON123456" \
  -H "Authorization: ABCD1234"
```
*Response:*
```
{"status":true}
```

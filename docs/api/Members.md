[Back to API list](../API.md)

# Convention Users

This API allows organizations participating in a convention to export their membership lists to the ConTroll
service, allowing members to get coupons and other benefits managed through the ConTroll service.

This API is currently only available when the authorized user is a convention manager or through convention
authorization (convention web site integration with API key).

## API

### List All Membership Records

`GET /entities/members`

**Input:** No input is required  
**Output:** An array of property lists, each containing the details of a user membership record, with the following fields:
* `id`: The system id for the membership record
* `user`: The user record of the member
* `organizer` : the organization record of the membership
* `membership` : membership code/number/registration

*Example:*
```
$ curl -X GET "http://api.con-troll.org/entities/members" \
  -H "Content-Type: application/json" \
  -H "Convention: CON123456" \
  -H "Authorization: ABCD1234"
```
*Response:*
```
[{"id": "501", "user": { "email": "oded@geek.co.il", "name": "Oded Arbel" }, 
"organizer" : { "title": "The Association" }, "membership": "10001" }]
```

### Create A Membership Record

`POST /entities/members`

**Input:** A property list specifing the membership record data, with the following fields:
* `user`: A user specification for the new record. Should be a property list with either an "id"
  field or an "email" field, specifying the user, but not both.
* `organizer` : A specification of the organization whose memebership is being recorded. Can be
  either the organization ID number, or its title as it appears in the ConTroll service
* `membership` : membership code/number/registration

**Output:** An array of property lists, each containing the details of a user membership record, with the following fields:
* `id`: The system id for the membership record
* `user`: The user record of the member
* `organizer` : the organization record of the membership
* `membership` : membership code/number/registration

*Example:*
```
$ curl -X POST "http://api.con-troll.org/entities/members" \
  -H "Content-Type: application/json" \
  -H "Convention: CON123456" \
  -H "Authorization: ABCD1234" \
  -d '{"user":{"email":"oded@geek.co.il"},"organizer":1,"membership":"10001"}'
```
*Response:*
```
{ "id": "501", "user": { "email": "oded@geek.co.il", "name": "Oded Arbel" }, 
"organizer" : { "title": "The Association" }, "membership": "10001" }
```

### Get A Membership Record

`GET /entities/members/:id`

**Input:** Membership record ID in the request URI  
**Output:** A property list containing the details of a user membership record, with the following fields:
* `id`: The system id for the membership record
* `user`: The user record of the member
* `organizer` : the organization record of the membership
* `membership` : membership code/number/registration

*Example:*
```
$ curl -X GET "http://api.con-troll.org/entities/members/501" \
  -H "Content-Type: application/json" \
  -H "Convention: CON123456" \
  -H "Authorization: ABCD1234"
```
*Response:*
```
{"id": "501", "user": { "email": "oded@geek.co.il", "name": "Oded Arbel" }, 
"organizer" : { "title": "The Association" }, "membership": "10001"}
```

### Delete A Membership Record

`DELETE /entities/members/:id`

**Input:** Membership record ID in the request URI  
**Output:** A property list with a status field specifying if the operation was successful or not:
* `status`: boolean value specifying if the operation was successful
* `error`: (optional) An error message specifying the problem if the operation was not successful

*Example:*
```
$ curl -X DELETE "http://api.con-troll.org/entities/members/1" \
  -H "Content-Type: application/json" \
  -H "Convention: CON123456" \
  -H "Authorization: ABCD1234"
```
*Response:*
```
[ "status": true ]
```

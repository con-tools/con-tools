[Back to API list](../API.md)

# System Tags

A convention can specify tags to record custom data on their events, for fields that are different from a convention
to convention. For example one convention might need to record which rule system each game will use, while another
might need to record if a film is a fan-made or an official production.

To support this, ConTroll offers an extensive tagging support - the convention manager can create tag types and
assign different specification to them according to the convention needs.

## Anatomy Of Tagging

To add additional fields to an event, a manager creates "tag types" Each tag type has a title that is used to
describe the field that is needed, and a "requirement specification" which describes how that tag is used to create
event fields. The following requirement specifications are supported:

* `1` - One Required: An event must include a value for this tag, and exactly one such value.
* `*` - Any (Optional): An event can include any number of values for this tag, or even not have any values.
* `+` - One Or More: An event must include at least one value for this tag, but more are allowed.

Additionally, for each tag type, the convention manager can specify if the tag type is "public" - i.e. tag values
will be displayed for events in public displays; or "private" - i.e. only managers can see the values for these types.

Valid value lists can then be built for each "tag type".

Finally, events are associated with a value for each tag type (or several values, or no values - according to the
requirement spec for the type).

This construction allows the convention manager to review the use of tags, correct typing errors, coalece duplicate 
values, etc.

## API

All API calls for the system tags entities require a convention identification.  
Calls to modify tag types require convention manager's user authorization.  
Calls to modify tag values require user authorization, and if the tag type for which the value is about to be changed
is private, then the authorizing user must also be a convention manager.

Additional authorization concerns may be spepcified for each API call.

### List All Tag Types

`GET /entities/tagtypes`

If this call is done with a convention manager's user authorization, then private tag types are also listed, otherwise 
only public tag types will be listed.

**Input:** No input is required.  
**Output:** An array of property list, each containing the details of a tag type, containing the following fields
* `title`: The tag type title
* `requirement`: The tag type requirement specification
* `public` : (boolean) Whether the tag type is public or private

*Example:*
```
$ curl -X GET "http://localhost:8080/entities/managers" \
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
curl -X POST "http://localhost:8080/entities/managers" \
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
curl -X DELETE "http://localhost:8080/entities/managers/1" \
  -H "Content-Type: application/json" \
  -H "Convention: CON123456" \
  -H "Authorization: ABCD1234"
```
*Response:*
```
{"status":true}
```

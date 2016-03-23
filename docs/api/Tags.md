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

## Free Form Tagging (Taxonomy)

Many users like to use free form tagging to create more flexible taxonomies. The system tags feature in ConTroll allows
for that and provides much needed control to the convention manager, allowing them to easily remove duplicates, correct
spelling mistakes, etc.

To use a free form tags, simply create a public system tag named "taxonomy" (this name has no special meaning, except for
client applications that can use this tag and show a free tagging UI), with a requirement specification of `*` (allowing
as many tags as needed, including no tags). Users can create taxonomy values as they see fit, while the convention manager
can continue to use the advanced utility of the system tag update to control the free taxonomy.

## API

All API calls for the system tags entities require a convention identification.  
Calls to modify tag types require convention manager's user authorization.  
Calls to modify tag values require user authorization, and if the tag type for which the value is about to be changed
is private, then the authorizing user must also be a convention manager.

Additional authorization concerns may be specified for each API call.

### List All Tag Types

`GET /entities/tagtypes`

If this call is done with a convention manager's user authorization, then private tag types are also listed, otherwise 
only public tag types will be listed.

**Input:** No input is required.  
**Output:** An array of property list, each containing the details of a tag type, containing the following fields
* `title`: The tag type title
* `requirement`: The tag type requirement specification
* `public` : (boolean) Whether the tag type is public or private
* `values`: an array containing all known values for this tag type

*Example:*
```
$ curl -X GET "http://api.con-troll.org/entities/tagtypes" \
  -H "Content-Type: application/json" \
  -H "Convention: CON123456" \
  -H "Authorization: ABCD1234"
```
*Response:*
```
[{"title":"event-type","requirement":"1","public":true,"values":["Role playing game","Panel","Lecture"]},
{"title":"age-restriction","requirement":"1","public":true,"values":["None","!6 and up"]},
{"title":"taxonomy","requirement":"*","public":true,"values":["game","rpg","d&d"]}]
```

### Retreieve A Tag Type

`GET /entities/tagtypes/:title`

Retrieve a single tag type and its associated values. If this call is done with an authorized user that is a
convention manager, a private tag type can be retrieved, otherwise only public tag types can be retrieved.

**Input:** The tag type title as the URI parameter  
**Output:** A property list showing the created tag type content:
* `title`: The tag type title
* `requirement`: The tag type requirement specification
* `public` : (boolean) Whether the tag type is public or private
* `values`: an array containing all known values for this tag type

*Example:*
```
curl -X GET "http://api.con-troll.org/entities/tagtypes/event-type" \
  -H "Content-Type: application/json" 
    -H "Convention: CON123456" \
  -H "Authorization: ABCD1234"
```
*Response:*
```
{"title":"event-type","requirement":"1","public":true,"values":["Role playing game","Panel","Lecture"]}
```

### Add A Tag Type

`POST /entities/tagtypes`

Create a new tag type, possibly populating its value list.

**Input:** A property list with the following fields:
* `title` : The title to set for the tag type
* `requirement` : (optional) The requirement specification for the tag type. If not specified, default to '1'
* `public` : (boolean, optional) Whether that tag type is public or not. Default to `true`
* `values`: (optional) an array of valid values to associate with the tag type
**Output:** A property list showing the created tag type content:
* `status`: (boolean) whether the operation was successful or not
* `error`: (optional) error text describing the error if the operation was not successful
* `title`: The tag type title
* `requirement`: The tag type requirement specification
* `public` : (boolean) Whether the tag type is public or private
* `values`: an array containing all known values for this tag type

*Example:*
```
curl -X POST "http://api.con-troll.org/entities/tagtypes" \
  -H "Convention: CON123456" \
  -H "Authorization: ABCD1234" \
  -H "Content-Type: application/json" 
   -d '{"title":"event-type","values":["Role playing game","Panel","Lecture"]}' 
```
*Response:*
```
{"status":true,"title":"event-type","requirement":"1","public":true,"values":["Role playing game","Panel","Lecture"]}
```

### Update A Tag Type

`PUT /entities/tagtypes/:title`

Update the public visibility of a tag type or its values. Other fields of a tag type cannot be modified once created, 
so the only other option is to remove the tag and recreate it.

Convention manager user authorization is required for this operation.

**Input:** The tag type title as the URI parameter and a property list with the following fields:
* `public` : (boolean, optional) if set, will change the public visibility of the tag type
* `values` : (optional) an array of tag values to add to the tag type. This will not duplicate existing values.
* `remove-values` : (optional) an array of tag values to delete. If there are exiting events that have this value set, the operation
  will fail.
* `replace-values` : (optional) a property list of values to replace, where the key is the value to be removed from the system
  (and events that are using it) and the value for each key is the tag value to replace it with. The new value will be created if
  needed.
** Output:** A property list showing the created tag type content:
* `status`: (boolean) whether the operation was successful or not
* `error`: (optional) error text describing the error if the operation was not successful
* `title`: The tag type title
* `requirement`: The tag type requirement specification
* `public` : (boolean) Whether the tag type is public or private
* `values`: an array containing all known values for this tag type

*Example:*
```
curl -X PUT "http://api.con-troll.org/entities/tagtypes/event-type" \
  -H "Content-Type: application/json" \
  -H "Convention: CON123456" \
  -H "Authorization: ABCD1234" \
  -d '{"values": ["Workshop","Lecture"],"remove-values":["Panel"],"replace-values":{"Role playing game": "RPG"}}'
```
*Response:*
```
{"status":true,"title":"event-type","requirement":"1","public":true,"values":["RPG","Workshop","Lecture"]}
```

### Delete Tag Type

`DELETE /entities/tagtypes/:title`

Delete a tag type, so it can no longer be used with events. Deleting a tag type will also delete all values
associated with that tag, and can only be done if there are no events that are using this type.

If there any events that use this tag type, the delete will fail and the error message will note which events
are still using this tag type and should be updated before removing this tag type

**Input:** The tag type title as the URI parameter
**Output:** A property list showing the results of the operation with the following fields:
* `status`: A boolean value indicating whether the operation succeeded or not
* `error`: An error message in case the operation did not succeed, describing the problem.

*Example:*
```
curl -X DELETE "http://api.con-troll.org/entities/tagtypes/age-requirement" \
  -H "Content-Type: application/json" \
  -H "Convention: CON123456" \
  -H "Authorization: ABCD1234"
```
*Response:*
```
{"status":true}
```

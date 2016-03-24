[Back to API list](../API.md)

# Event Management

Events are managed as a data entity and supports GET (retrieve), POST (create), PUT (update) and DELETE (delete).

Some methods require both user authentication and convention public identification (i.e. select the convention for which
to apply the operation using the convention key), while other require only convention public identification.

## API

### Create Event

`POST /entities/events`

This method requires a convention identity and a user authorization. The submitting user becomes the event owner and
point of contact. Alternatively, if the authorized user is a convention manager, they can specify a different event
owner by including in the input field named `user` with a value of a property list which can include either the field
`id` whose value is a user's system id number or a field `email` whose value is a user's e-mail address.

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
* `user`: a property list describing the user that will be set as the event owner and PoC. only
	supported if the authorizing user is a convention manager, otherwise its an error to provide this
	field.
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

### Retrieve An Event

`GET /entities/events/:id`

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

### List All Events (Catalog)

`GET /entities/events`

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

### Cancel An Event

`DELETE /entities/events/:id`

This method requires a convention public identity and a user authorization for a user that is a manager
in the convention.

The event is not actually deleted, it is just moved immediately to the `CANCELLED` status where it is not
shown in any public list.

**Input:** The id of the event must be specified in the URI  
**Output:** A property list containing the status of the operation:
* `status`: boolean value set to `true` if the operation was successful.
* `error`: the error message if the operation has failed

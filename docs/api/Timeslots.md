[Back to API list](../API.md)

# Time Slots

Time slots are the basis for the scheduling system of ConTroll. When an event (a description of something that happens),
a location (where something happen), and hosts (who makes something happen) come together - you get a time slot. A manager
creates the schedule of the convention by creating time slots and applying attributes to them. Most attributes are inherited
from the event, but some attributes are only copied and can be modified per time slot. 

## API

All API calls for the time slot entities require a convention identification and convention manager user authorization,
except catalog and retrieve.

### List All Time Slots (Catalog)

`GET /entities/timeslots`

**Input:** No input is required.  
**Output:** An array of property list, each containing the details of a time slot, the event that happens there, the locations
where it happens and who hosts the time slot - containing the following fields:
* `id`: time slot id for the created time slot
* `duration`: duration of the time slotted event (can be different than the event's)
* `min-attendees`: minimum number of attendees in the time slotted event (can be different than the event's)
* `max-attendees`: maximum number of attendees in the time slotted event (can be different than the event's)
* `notes-to-attendees`: notes for attendees of the time slotted event (can be different than the event's)
* `event`: event details as can also be seen using the event API
* `locations`: list of locations where the time slotted event happens, the same as can be seen using the locations API
* `hosts`: list of event hosts for this time slot, each presented as a property list with the fields `name` and `email`

*Example:*
```
$ curl -X GET "http://api.con-troll.org/entities/timeslots" \
  -H "Content-Type: application/json" \
  -H "Convention: CON123456"
```
*Response:*
```
[{"id":"2","duration":"60","min-attendees":"3","max-attendees": "4","notes-to-attendees": "Hooray!",
"event":{ <skipped for bravity, see events API for details> },"start": "2016-04-27T10:00:00-02:00",
"locations":[{<skipped for bravity, see locations API for details>}],"hosts":[{"name":"Oded Arbel","email":"oded@geek.co.il"}]},
"id":"3","duration":"240","min-attendees":"2","max-attendees": "10","notes-to-attendees": null,
"event":{ <skipped for bravity, see events API for details> },"start": "2016-04-27T10:00:00-02:00",
"locations":[{<skipped for bravity, see locations API for details>}],"hosts":[{"name":"Oded Arbel","email":"oded@geek.co.il"}]}]
```

### Retrieve A Time Slot

`GET /entities/timeslots/:id`

Retrieve information for a single time slot, the event that happens there, the locations where it happens and who hosts it.

**Input:** The time slot id as the URI parameter  
**Output:** A property list containing the details of a location, with the following fields:
* `id`: time slot id for the created time slot
* `duration`: duration of the time slotted event (can be different than the event's)
* `min-attendees`: minimum number of attendees in the time slotted event (can be different than the event's)
* `max-attendees`: maximum number of attendees in the time slotted event (can be different than the event's)
* `notes-to-attendees`: notes for attendees of the time slotted event (can be different than the event's)
* `event`: event details as can also be seen using the event API
* `locations`: list of locations where the time slotted event happens, the same as can be seen using the locations API
* `hosts`: list of event hosts for this time slot, each presented as a property list with the fields `name` and `email`

*Example:*
```
curl -X GET "http://api.con-troll.org/entities/timeslots/456" \
  -H "Content-Type: application/json" 
  -H "Convention: CON123456"
```
*Response:*
```
{"id":"2","duration":"60","min-attendees":"3","max-attendees": "4","notes-to-attendees": "Hooray!",
"event":{ <skipped for bravity, see events API for details> },"start": "2016-04-27T10:00:00-02:00",
"locations":[{<skipped for bravity, see locations API for details>}],"hosts":[{"name":"Oded Arbel","email":"oded@geek.co.il"}]}
```

### Schedule A New Time Slot

`POST /entities/timeslots`

Schedule a new time slot. This API call requires convention manager user authorization.

**Input:** A property list with the following fields:
* `event`: the id of event that is being scheduled
* `start`: (string, integer) the time in which the event is scheduled. Supported formats are Unix epoch time as
  a number, or ISO-8601 formatted time string.
* `duration`: (optional, integer) override the duration of the event with a new duration value in minutes
* `min_attendees`: (optional, integer) override the minimum number of attendees from the event with a new value
* `max_attendees`: (optional, integer) override the maximum number of attendees from the event with a new value
* `notes_to_attendees`: (optional) override the note to attendees from the event with a new value
* `locations`: specify a list of slugs of locations where the time slot takes place. At least one location must be provided.
* `hosts`: (optional) specify a list of users that will be the hosts of the event, for each user specify a property
  list with either an `id` field containing the user id or an `email` field containing the user's email. If this 
  field is not specified, the event owner is added by default as the time slot host.  
**Output:** A property list showing the created time slot information, with the following fields:
* `status`: (boolean) whether the operation was successful or not
* `error`: (optional) error text describing the error if the operation was not successful
* `id`: time slot id for the created time slot
* `duration`: duration of the time slotted event (can be different than the event's)
* `min-attendees`: minimum number of attendees in the time slotted event (can be different than the event's)
* `max-attendees`: maximum number of attendees in the time slotted event (can be different than the event's)
* `notes-to-attendees`: notes for attendees of the time slotted event (can be different than the event's)
* `event`: event details as can also be seen using the event API
* `locations`: list of locations where the time slotted event happens, the same as can be seen using the locations API
* `hosts`: list of event hosts for this time slot, each presented as a property list with the fields `name` and `email`

*Example:*
```
curl -X POST "http://api.con-troll.org/entities/timeslots" \
  -H "Convention: CON123456" \
  -H "Authorization: ABCD1234" \
  -H "Content-Type: application/json" 
  -d '{"event":7,"start":1458651705,"max_attendees":4,"notes_to_attendees":"Hooray!","locations":["table-1"],
  "hosts":[{"email":"oded@geek.co.il"}]}' 
```
*Response:*
```
{"status":true,"id":"2","duration":"60","min-attendees":"3","max-attendees": "4","notes-to-attendees": "Hooray!",
"event":{ <skipped for bravity, see events API for details> },"start": "2016-04-27T10:00:00-02:00",
"locations":[{<skipped for bravity, see locations API for details>}],"hosts":[{"name":"Oded Arbel","email":"oded@geek.co.il"}]}
```

### Update a Scheduled Time Slot

`PUT /entities/timeslots/:id`

A time slot can be updated by changing the start time, duration, min and max attendees, notes to staff, and by adding or
removing locations and hosts. This API call requires convention manager user authorization.

**Input:** The time slot id as the URI parameter, and in addition a property list object with the following fields:
* `start`: (optional, string, integer) updated start time in which the event is scheduled. Supported formats are Unix
  epoch time as a number, or ISO-8601 formatted time string.
* `duration` (optional, integer) updated time slotted event duration in minutes
* `min-attendees`: (optional, integer) updated minimum number of attendees in the time slotted event
* `max-attendees`: (optional, integer) updated maximum number of attendees in the time slotted event
* `notes-to-attendees`: (optional) updated notes for attendees of the time slotted event
* `locations`: (optional) list of slugs of location to add to the list of locations where the event is time slotted.
* `remove-locations`: (optional) list of slugs of location to remove from the list of locations where the event is time
  slotted. It is not valid to remove all locations.
* `hosts`: (optional) a list of users that will be the added to the host list of the time slot, for each user specify a
  property list with either an `id` field containing the user id or an `email` field containing the user's email.
* `remove-hosts`: (optional) a list of users that will be removed from the host list of the time slot. Specify users as
  in the `hosts` field. If no users are left in the host list, the event owner will be automatically added as a host.
**Output:** A property list showing the updated time slot information, with the following fields:
* `status`: (boolean) whether the operation was successful or not
* `error`: (optional) error text describing the error if the operation was not successful
* `id`: time slot id for the created time slot
* `duration`: duration of the time slotted event (can be different than the event's)
* `min-attendees`: minimum number of attendees in the time slotted event (can be different than the event's)
* `max-attendees`: maximum number of attendees in the time slotted event (can be different than the event's)
* `notes-to-attendees`: notes for attendees of the time slotted event (can be different than the event's)
* `event`: event details as can also be seen using the event API
* `locations`: list of locations where the time slotted event happens, the same as can be seen using the locations API
* `hosts`: list of event hosts for this time slot, each presented as a property list with the fields `name` and `email`

*Example:*
```
curl -X POST "http://api.con-troll.org/entities/timeslots" \
  -H "Convention: CON123456" \
  -H "Authorization: ABCD1234" \
  -H "Content-Type: application/json" 
  -d '{"start":"2016-04-27T15:00:00-02:00","max_attendees":10,
  "locations":["table-4"],"remove-locations":["table-1"],
  "hosts":[{"id":17}],"remove-hosts":[{"email":"oded@geek.co.il"}]}' 
```
*Response:*
```
{"status":true,"id":"2","duration":"60","min-attendees":"3","max-attendees": "10","notes-to-attendees": "Hooray!",
"event":{ <skipped for bravity, see events API for details> },"start": "2016-04-27T15:00:00-02:00",
"locations":[{<skipped for bravity, see locations API for details>}],"hosts":[{"name":"Conan","email":"conan@example.com"}]}
```



### Cancel A Scheduled Time Slot

`DELETE /entities/timeslots/:id`

Cancel the scheduling of a time slot in the convention. This API call requires convention manager user authorization.

**Input:** The time slot id as the URI parameter  
**Output:** A property list showing the results of the operation with the following fields:
* `status`: A boolean value indicating whether the operation succeeded or not
* `error`: An error message in case the operation did not succeed, describing the problem.

*Example:*
```
curl -X DELETE "http://api.con-troll.org/entities/timeslots/456" \
  -H "Content-Type: application/json" \
  -H "Convention: CON123456" \
  -H "Authorization: ABCD1234"
```
*Response:*
```
{"status":true}
```

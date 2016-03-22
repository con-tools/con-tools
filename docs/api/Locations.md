[Back to API list](../API.md)

# Locations

Locations are where events occur (or more precisely, where events are scheduled - or time-slotted).

## API

All API calls for the location entities require a convention identification and convention manager user authorization,
except catalog and retrieve.

### List All Locations (Catalog)

`GET /entities/locations`

**Input:** No input is required.  
**Output:** An array of property list, each containing the details of a location, containing the following fields:
* `title` : the title to for the location
* `area` : (optional) a general description of the area where the location can be found, such as "main hall" or "green rooms"
* `max_attendees` : (integer) the maximum number of people that can be hosted in that location
* `slug`: (optional) a URL friendly name for the location

*Example:*
```
$ curl -X GET "http://localhost:8080/entities/locations" \
  -H "Content-Type: application/json" \
  -H "Convention: CON123456"
```
*Response:*
```
[{"status":true,"title":"Table 2","slug":"table-2","area":"Main Hall"}]
```

### Retreive A Location

`GET /entities/locations/:slug`

Retrieve information of a single location.

**Input:** The location slug as the URI parameter  
**Output:** A property list containing the details of a location, with the following fields:
* `title` : the title to for the location
* `area` : (optional) a general description of the area where the location can be found, such as "main hall" or "green rooms"
* `max_attendees` : (integer) the maximum number of people that can be hosted in that location
* `slug`: (optional) a URL friendly name for the location

*Example:*
```
curl -X GET "http://localhost:8080/entities/locations/table-2" \
  -H "Content-Type: application/json" 
  -H "Convention: CON123456"
```
*Response:*
```
{"status":true,"title":"Table 2","slug":"table-2","area":"Main Hall"}
```

### Add A Location

`POST /entities/locations`

Create a new location. This API call requires convention manager user authorization.

Both title and slug (if provided) must be unique in the convention.

**Input:** A property list with the following fields:
* `title` : the title to for the location
* `area` : (optional) a general description of the area where the location can be found, such as "main hall" or "green rooms"
* `max_attendees` : (integer) the maximum number of people that can be hosted in that location
* `slug`: (optional) use a pre-generated slug (URL friendly name) for the location, instead of generating one automatically
**Output:** A property list showing the created location information, with the followign fields:
* `status`: (boolean) whether the operation was successful or not
* `error`: (optional) error text describing the error if the operation was not successful
* `title` : the title to for the location
* `area` : (optional) a general description of the area where the location can be found, such as "main hall" or "green rooms"
* `max_attendees` : (integer) the maximum number of people that can be hosted in that location
* `slug`: (optional) a URL friendly name for the location

*Example:*
```
curl -X POST "http://localhost:8080/entities/locations" \
  -H "Convention: CON123456" \
  -H "Authorization: ABCD1234" \
  -H "Content-Type: application/json" 
  -d '{"title": "Table 2","area": "Main Hall","max_attendees": 6}' 
```
*Response:*
```
{"status":true,"title":"Table 2","slug":"table-2","area":"Main Hall"}
```

### Delete Location

`DELETE /entities/locations/:slug`

Delete a location from the convention. This API call requires convention manager user authorization.

If there any event time slots that are scheduled in this location, the delete will fail and the error
message will note which time slots are still using this location and should be updated before removing this location.

**Input:** The location slug as the URI parameter  
**Output:** A property list showing the results of the operation with the following fields:
* `status`: A boolean value indicating whether the operation succeeded or not
* `error`: An error message in case the operation did not succeed, describing the problem.

*Example:*
```
curl -X DELETE "http://localhost:8080/entities/locations/table-2" \
  -H "Content-Type: application/json" \
  -H "Convention: CON123456" \
  -H "Authorization: ABCD1234"
```
*Response:*
```
{"status":true}
```

[Back to API list](../API.md)

# Convention Management

Conventions are managed as a data entity and supports GET (retrieve) and POST (create)

## API

### Create A New Convention And Client Key

`POST /entities/conventions`

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

### Retrieve public convention information

`GET /entities/conventions/<id>`


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

### List All Conventions (Catalog)

`GET /entities/conventions`


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

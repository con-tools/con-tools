# Data Model

This document describes the data model used by ConTroll. The data model is
currently implemented using MySQL relational database and uses foreign keys
to link records in a logical hierarchy.

## Implementation

The model is implemented using Kohana ORM with a few local modifications. The
models are implemented as `Model_*` classes that extend `ORM` which is
extended locally in the application.

## Model Conventions

Generally ConTroll model classes follow Kohana ORM conventions and rely on the
ORM module's default behavior whenever possible.
 
In addition to the regular metadata that Kohana's `ORM` module expects
(relationship data and customizations of default behaviors), model classes
should also implement a protected field called `$_columns` that contains
a map of all table columns used to store data to a declaration of their
data type. This is used by the local ORM extension to provide on the fly
conversions for some datatypes such as dates as well as some validation
checks. Most column types will not require such extended behavior, in which
case the data type description can be the empty array (`[]`) to specify
the default behavior.

## Model Documentation

### User

Stores user identity

*Model Name:* User
*Fields:*
* Name
* Email
* Phone number
* Date of birth
* Authentication/Identity provider (if using the built-in password database,
  will be set to "password")
* Password (The hashed password for users of the built-in password database or
  the identity token for an external authentication provider)
* Time created
* Last login time

### Convention

Conventions that are managed in ConTroll

*Model Name:* Convention
*Fields:*
* Title
* Slug - A short code used in permalink URLs
* Series - The name of the series of conventions for a recurring convention
* Website - The URL for the convention web site
* Location - A textual description of the location where the convention is held
* Start Date - Date/Time the convention starts
* End Date - Date/Time the convention ends

### Organizer

An organization that works/creates/owns a convention

*Model Name:* Organizer
*Fields:*
* Title

TODO: Specify how this is connected to `Convention` (link table?)

### Location

A location in a convention where events are held, such as a lecture room, table
in a main hall, etc.

*Model Name:* Location
*Fields:*
* Title
* Max Attendees - the max occupancy of the location
* Area - description of where that location can be found, such as "Main hall",
  "Building B, 2nd Floor", etc.
* Convention (reference)

### Time Slot

A Time slot is a scheduling of an event to a location and time

*Model Name:* Timeslot
*Fields:*
* Event (reference)
* Start Time
* Duration
* Minimum Attendees
* Maximum Attendees
* Notes To Attendees
* Pass Requirement (reference)

### Passes

A pass is a ticket that grants pass requirements (one or more) to users

*Model Name:* Pass  
*Fields:*
* Title
* Price

### Pass Requirements

A definition of access to a class of time slots - users can receive "Pass
Requirements" to allow them access to all time slots that require that
instance.

*Mode Name:* Pass_Requirement
*Fields:*
* Title

### User Pass Requirements

Collection of all pass requirements collected by users (many to many)

*Model Name:* User_Pass_Requirement
*Fields:*
* User (reference)
* Pass Requirement (reference)

### Pass Pass Requirements

Collection of all pass requirements provided by passes (many to many)

*Mode Name:* Pass_Pass_Requirement
*Fields:*
* Pass (reference)
* Pass Requirement (reference)

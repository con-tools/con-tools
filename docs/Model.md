# Data Model

This document describes the data model used by ConTroll. The data model is
currently implemented using MySQL relational database and uses foreign keys
to link records in a logical hierarchy.

# User

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


# Time Slot

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

# Passes

A pass is a ticket that grants pass requirements (one or more) to users

*Model Name:* Pass  
*Fields:*
* Title
* Price

# Pass Requirements

A definition of access to a class of time slots - users can receive "Pass
Requirements" to allow them access to all time slots that require that
instance.

*Mode Name:* Pass_Requirement
*Fields:*
* Title

# User Pass Requirements

Collection of all pass requirements collected by users (many to many)

*Model Name:* User_Pass_Requirement
*Fields:*
* User (reference)
* Pass Requirement (reference)

# Pass Pass Requirements

Collection of all pass requirements provided by passes (many to many)

*Mode Name:* Pass_Pass_Requirement
*Fields:*
* Pass (reference)
* Pass Requirement (reference)

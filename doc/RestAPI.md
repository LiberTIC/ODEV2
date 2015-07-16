### `GET` /api/ ###

_Index of the API_


### `GET` /api/calendar/ ###

_Redirect to /api/calendar/list_


### `POST` /api/calendar/ ###

_Create a new calendar_

#### Requirements ####

**displayname**

  - Type: string
  - Description: The name of the calendar
**username**

  - Type: string
  - Description: The name of the owner of the calendar

#### Parameters ####

description:

  * type: string
  * required: false
  * description: The description of the calendar


### `GET` /api/calendar/list/ ###

_List all calendars_


### `GET` /api/calendar/{uri} ###

_Retrieve the calendar with the given uri_

#### Requirements ####

**uri**

  - Type: string
  - Description: The uri of the calendar


### `PUT` /api/calendar/{uri} ###

_Update the calendar with the given uri_

#### Requirements ####

**uri**

  - Type: string
  - Description: The uri of the calendar

#### Parameters ####

displayname:

  * type: string
  * required: false
  * description: The name of the calendar

description:

  * type: string
  * required: false
  * description: The description of the calendar


### `DELETE` /api/calendar/{uri} ###

_Delete the calendar with the given uri_

#### Requirements ####

**uri**

  - Type: string
  - Description: The uri of the calendar


### `GET` /api/calendar/{uri}/events/ ###

_List all events of a calendar_

#### Requirements ####

**uri**

  - Type: string
  - Description: The uri of the calendar


### `GET` /api/event/ ###

_Redirect to /api/event/list_


### `POST` /api/event/ ###

_Create a new event_

#### Requirements ####

**calendar_uri**

  - Type: string
  - Description: The uri of the calendar

**event_data**

  - Type: array
  - Description: All the fields of the new event


### `GET` /api/event/list/ ###

_List all events_


### `GET` /api/event/{uriEvent} ###

_Retrieve the event with the given uri_

#### Requirements ####

**uriEvent**

  - Type: string
  - Description: The uri of the event


### `PUT` /api/event/{uriEvent} ###

_Update the event with the given uri_

#### Requirements ####

**uriEvent**

  - Type: string
  - Description: The uri of the event

#### Parameters ####

some_property:

  * type: string
  * required: false
  * description: Some property to update


### `DELETE` /api/event/{uriEvent} ###

_Delete the event with the given uri_

#### Requirements ####

**uriEvent**

  - Type: string
  - Description: The uri of the event

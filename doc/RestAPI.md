## Rest API

#### Calendar

**Index**

```
GET /api/calendar
```

Redirect to /api/calendar/list

**List**

```
GET /api/calendar/list
```

Return the list of all calendar of the server. Response is formatted as follow:

```
{
    "calendars": [
        {
            "uri": "default",
            "displayname": "default"
        },
        {
            "uri": "someCalendar",
            "displayname": "Some Calendar"
        },
        ...
    ]
}
```

**By uri**

```
GET /api/calendar/{uri}
```

Return the calendar corresponding the given uri. Response is formatted as follow:

```
{
    "calendar": {
        "id": 42,
        "principaluri": "principals\/admin",
        "displayname": "default",
        "uri": "default",
        "synctoken": 1337,
        ...
    }
}
```
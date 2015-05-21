## Rest API

*Note: Currently, API only handles json, but new formats should be implemented in a not so distant future*

*Note: For future developement: http://williamdurand.fr/2012/08/02/rest-apis-with-symfony2-the-right-way/*

#### Calendar

**List calendars**

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

**Get calendar by uri**

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

**List events of a calendar**

```
GET /api/calendar/{uri}/event/list
```

Return the list of all events of a calendar. Response is formatted as follow:

```
{
    "events": [
        {
            "uri": "9D515E5D-E1D7-4982-A125-A32E74C7BD55.ics",
            "calendaruri": "default",
            "etag": "e1c27a9442524e5641f8039216420454"
        },
        {
            "uri": "697588A2-2660-4460-9B63-63B0FA70480C.ics",
            "calendaruri": "default",
            "etag": "8e56913f6e36c0ad12c24f6eeb651c3c"
        },
        ...
    ]
}
```

**Get event by its uri and its calendar-uri**

```
GET /api/calendar/{uri}/event/{uriEvent}
```

Return the event corresponding the given uris. Response is formatted as follow:

```
{
    "event": {
        "uri": "697588A2-2660-4460-9B63-63B0FA70480C.ics",
        "etag": "8e56913f6e36c0ad12c24f6eeb651c3c",
        "vobject": [
            "vcalendar",
            [
                [
                    "version",
                    [],
                    "text",
                    "2.0"
                ],
                ...
            ],
            ...
        ]
    }
}
```

*Note: vobject use the [jCal](http://tools.ietf.org/html/rfc7265) format*


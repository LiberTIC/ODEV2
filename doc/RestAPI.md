## Rest API

*Note: Currently, API only handles json, but new formats should be implemented in a not so distant future*

*Note: For future developement: http://williamdurand.fr/2012/08/02/rest-apis-with-symfony2-the-right-way/*

### Calendar
------------

**List calendars**

```
GET /api/calendar/list
```

Return the list of all calendar of the server. Response is formatted as follow:

```
{
    "count": 14,
    "calendars": [
        {
            "uri": "default",
            "displayname": "default",
            "links": [
                {
                    "rel": "self",
                    "href": "http://localhost:8000/api/calendar/default"
                }
            ]
        },
        {
            "uri": "somecalendar",
            "displayname": "Some Calendar",
            "links": [
                {
                    "rel": "self",
                    "href": "http://localhost:8000/api/calendar/somecalendar"
                }
            ]
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
        "displayname": "default",
        "uri": "default",
        "synctoken": 1337,
        "description": "Bla bla bla",
        "links": [
            { "rel": "self", "href": "http://localhost:8000/api/calendar/default" },
            { "rel": "events", "href": "http://localhost:8000/api/calendar/default/events/" },
            { "rel": "owner", "href": "not implemented yet" }
        ]
    }
}
```

**List events of a calendar**

```
GET /api/calendar/{uri}/events/
```

Return the list of all events of a calendar. Response is formatted as follow:

```
{
    "count": 34,
    "events": [
        {
            "uri": "9D515E5D-E1D7-4982-A125-A32E74C7BD55",
            "calendaruri": "default",
            "etag": "e1c27a9442524e5641f8039216420454",
            "links": [
                {
                    "rel": "self",
                    "href": "http://localhost:8000/api/event/9D515E5D-E1D7-4982-A125-A32E74C7BD55"
                },
                {
                    "rel": "calendar",
                    "href": "http://localhost:8000/api/calendar/default"
                }
            ]
        },
        {
            "uri": "697588A2-2660-4460-9B63-63B0FA70480C",
            "calendaruri": "default",
            "etag": "8e56913f6e36c0ad12c24f6eeb651c3c",
            ...
        },
        ...
    ]
}
```




### Events
-----------

**List events**

```
GET /api/event/list
```

Return the list of all events of the server. Response is formatted as follow:

```
{
    "count": 23,
    "events": [
        {
            "uri": "94857AE8-45FD-46FE-9857-C62086A05EE4",
            "calendaruri": "default",
            "etag": "e94073062a3cef2a0879244b8733e3fc",
            "links": [
                {
                    "rel": "self",
                    "href": "http://localhost:8000/api/event/94857AE8-45FD-46FE-9857-C62086A05EE4"
                },
                {
                    "rel": "calendar",
                    "href": "http://localhost:8000/api/calendar/default"
                }
            ]
        },
        ...
    ]
}
```


**Get event by its uri**

```
GET /api/event/{uriEvent}
```

Return the event corresponding the given uri. Response is formatted as follow:

```
{
    "event": {
        "uri": "9D515E5D-E1D7-4982-A125-A32E74C7BD55",
        "calendaruri": "default",
        "links": [
            {
                "rel": "self",
                "href": "http://localhost:8000/api/event/9D515E5D-E1D7-4982-A125-A32E74C7BD55"
            },
            {
                "rel": "calendar",
                "href": "http://localhost:8000/api/calendar/default"
            }
        ],
        "etag": "e1c27a9442524e5641f8039216420454",
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


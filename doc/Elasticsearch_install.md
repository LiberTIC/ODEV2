## Installation d'un serveur ElasticSearch

WIP...

### Config pour mapping du serveur:

```
PUT /app/
{
  "mappings": {
    "users": {
      "properties": {
        "usernameCanonical": {
          "type": "string",
          "index": "not_analyzed"
        },
        "emailCanonical": {
          "type": "string",
          "index": "not_analyzed"
        },
        "salt": {
          "type": "string",
          "index": "not_analyzed"
        },
        "password": {
          "type": "string",
          "index": "not_analyzed"
        },
        "passwordDigesta": {
          "type": "string",
          "index": "not_analyzed"
        }
      }
    }
  }
}
```
```
PUT /caldav
{
  "mappings": {
    "calendars": {
      "properties": {
        "principaluri": {
          "type": "string",
          "index": "not_analyzed"
        },
        "uri": {
          "type": "string",
          "index": "not_analyzed"
        }
      }
    },
    "calendarobjects": {
      "date_detection" : false,
      "properties": {
        "uri": {
          "type": "string",
          "index": "not_analyzed"
        },
        "etag": {
          "type": "string",
          "index": "not_analyzed"
        },
        "uid": {
          "type": "string",
          "index": "not_analyzed"
        },
        "url": {
          "type": "string",
          "index": "not_analyzed"
        }
      }
    },
    "calendarchanges": {
      "properties": {
        "uri": {
          "type": "string",
          "index": "not_analyzed"
        }
      }
    },
    "principals": {
      "properties": {
        "uri": {
          "type": "string",
          "index": "not_analyzed"
        },
        "email": {
          "type": "string",
          "index": "not_analyzed"
        },
        "displayname": {
          "type": "string",
          "index": "not_analyzed"
        },
        "vcardurl": {
          "type": "string",
          "index": "not_analyzed"
        }
      }
    }
  }
}
```
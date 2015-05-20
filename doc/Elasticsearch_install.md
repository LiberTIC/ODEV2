## Installation d'un serveur ElasticSearch

### Documentation

- [Official setup doc](https://www.elastic.co/guide/en/elasticsearch/reference/current/setup.html)
- [Digital Ocean doc](https://www.digitalocean.com/community/tutorials/how-to-install-elasticsearch-on-an-ubuntu-vps)

Mind that `Java 7 update 55` is not enough up-to-date for the last elastic release, on Ubuntu 12.10. Consider upgrading to Java8. See elastic log on error while trying to start it.

### Enable Marvel logging
```
marvel.agent.enabled: true
```

### Reduce network to localhost
```
network.bind_host: localhost
```

### Enable scripting
```
script.disable_dynamic: false
script.groovy.sandbox.enabled: true
```

### Enable Http Basic Auth
```
http.basic.enabled: true
http.basic.user: "ODE"
http.basic.password: "ultraSecretePasswordOfTheDead"
```

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


### Supprimer les logs

```
curator delete indices --older-than 2 --time-unit days --timestring '%Y.%m.%d' --prefix .marvel-
```

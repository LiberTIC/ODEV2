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

### Données de tests

```
POST /caldav/calendars/
{
  "principaluri" : "principals/admin",
  "displayname" : "default",
  "uri": "default",
  "synctoken": 1,
  "description": null,
  "calendarorder": 1,
  "calendarcolor": "#711A76FF",
  "timezone": null,
  "components": ["VEVENT","VTODO"],
  "transparent": 0
}

POST /caldav/principals/
{
  "uri": "principals/admin",
  "email": "admin@example.org",
  "displayname": "Administrator",
  "vcardurl": null
}

POST /caldav/principals/
{
  "uri": "principals/admin/calendar-proxy-read",
  "email": null,
  "displayname": null,
  "vcardurl": null
}

POST /caldav/principals/
{
  "uri": "principals/admin/calendar-proxy-write",
  "email": null,
  "displayname": null,
  "vcardurl": null
}

POST /app/users/
{
  "username": "admin",
  "usernameCanonical": "admin",
  "email": "admin@admin.fr",
  "emailCanonical": "admin@admin.fr",
  "enabled": true,
  "salt": "buk14ljud3scw4k80k8kcckks8gc8o0",
  "password": "mnvGN+JXg8jkCr+9AZg7fCP8BEbgGpoclogxp76h7kHg1wAstu8JwZmABhRQYHSUl+KpN4MJIAwXfYoLsMn7MQ==",
  "passwordDigesta": "87fd274b7b6c01e48d7c2f965da8ddf7",
  "lastLogin": null,
  "locked": false,
  "expired": false,
  "expires_at": null,
  "confirmationToken": null,
  "passwordRequestedAt": null,
  "roles": [],
  "credentialsExpired": false,
  "credentialsExpireAt": null
}
```

*Note: admin a pour mot de passe: "admin"*


### Supprimer les logs

```
curator delete indices --older-than 2 --time-unit days --timestring '%Y.%m.%d' --prefix .marvel-
```

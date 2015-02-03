

## Environment

```
Linux 3.2.58-xen x86_64 GNU/Linux
Ubuntu 12.10
2 Go RAM
```

`/etc/apt/sources.list`:
```
deb http://old-releases.ubuntu.com/ubuntu quantal main universe multiverse
deb http://old-releases.ubuntu.com/ubuntu quantal-updates main universe multiverse
deb http://old-releases.ubuntu.com/ubuntu quantal-security main universe multiverse
```

## Steps to reproduce

### [Follow this tutorial](https://www.deanspot.org/alex/2009/03/23/installing-apples-calendarserver-ubuntu.html)

```bash
$ sudo apt-get install calendarserver
```

- I escaped the `umount` part 
- I run `bin/run` instead of `/bin/dev`

Escaping the Debian 64bit issue, adding this into `/etc/caldavd/caldavd.plist`:

```xml
 <key>UseMetaFD</key>
 <false/>
```

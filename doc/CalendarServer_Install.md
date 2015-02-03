

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

Using Python 2.x-dev + [pip](https://pip.pypa.io/en/latest/installing.html)

Updated pip using `sudo pip install --upgrade setuptools`

```bash
$ sudo apt-get install calendarserver
```

- I escaped the `umount` part 
- I run `bin/run` instead of `/bin/dev`
- I did use HTTP (changed port from `8008` to regular `8443`

Because of the `[Errno 22] Invalid argument" in error.log when connecting to port 8008 on a fresh install` error, 
following this [Debian bug forum thread](https://bugs.debian.org/cgi-bin/bugreport.cgi?bug=678525), I escaped the Debian 64bit issue, adding this into `/etc/caldavd/caldavd.plist`:

```xml
 <key>UseMetaFD</key>
 <false/>
```

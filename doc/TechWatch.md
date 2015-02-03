# Technical Watch around ODE project

CalDAV servers

- http://sabre.io
- http://baikal-server.com

## Prototypes

- [polypodes/CalDAVClientPrototype](https://github.com/polypodes/CalDAVClientPrototype)

## Libraries


### PHP CalDAV clients

- [fruux/sabre-davclient](https://github.com/fruux/sabre-davclient)
- [CloudManaged/sabre-davclient](https://github.com/CloudManaged/sabre-davclient): most recent fork of fruux/sabre-davclient
- (oscar09/CalDAVClient)[https://github.com/oscar09/CalDAVClient)
- [BernsteinElektro/php-caldav-client](https://github.com/BernsteinElektro/php-caldav-client/blob/master/lib/BE/CalDav/Client.php)
- [wvrzel/simpleCalDAV](https://github.com/wvrzel/simpleCalDAV)

### Feedbacks on PHP CalDAV Client

#### [wvrzel/simpleCalDAV](https://github.com/wvrzel/simpleCalDAV)

- needed to be refactored to be [autolodable](http://www.php-fig.org/psr/psr-0/)
- easy to use
- poor performances (used with Baïkal, locally) 

### vCard & iCal

= Parsing and manipulating iCalendar and vCard objects:

- parse and manipulate iCalendar and vCard objects: https://github.com/fruux/sabre-vobject & http://sabre.io/vobject

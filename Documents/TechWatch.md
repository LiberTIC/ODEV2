# Technical Watch around ODE project

CalDAV servers

- http://sabre.io
- http://baikal-server.com

CalDAV clients
- Lightning de Mozilla
- iCalendar d'Apple

## Prototypes

- [polypodes/CalDAVClientPrototype](https://github.com/polypodes/CalDAVClientPrototype)

## Libraries


### PHP CalDAV clients

- [Davical PHP lib](http://www.davical.org) ([Git repo](http://repo.or.cz/w/davical.git) ; [example](http://www.aadl.org/node/261978))
- [fruux/sabre-davclient](https://github.com/fruux/sabre-davclient)
- [fruux/sabre-vobject](https://github.com/fruux/sabre-vobject)
- [CloudManaged/sabre-davclient](https://github.com/CloudManaged/sabre-davclient): most recent fork of fruux/sabre-davclient
- [oscar09/CalDAVClient](https://github.com/oscar09/CalDAVClient)
- [BernsteinElektro/php-caldav-client](https://github.com/BernsteinElektro/php-caldav-client/blob/master/lib/BE/CalDav/Client.php)
- [wvrzel/simpleCalDAV](https://github.com/wvrzel/simpleCalDAV)
- [Simplest PHP client ever](http://trentrichardson.com/2012/06/22/put-caldav-events-to-calendar-in-php/)
- [fzaninotto/Faker](https://github.com/fzaninotto/Faker)

### Feedbacks on PHP CalDAV Client

#### [wvrzel/simpleCalDAV](https://github.com/wvrzel/simpleCalDAV)

- needed to be refactored to be [autolodable](http://www.php-fig.org/psr/psr-0/)
- easy to use
- poor performances (used with Baïkal, locally) 

### vCard & iCal

= Parsing and manipulating iCalendar and vCard objects:

- parse and manipulate iCalendar and vCard objects: https://github.com/fruux/sabre-vobject & http://sabre.io/vobject

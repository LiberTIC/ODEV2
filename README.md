# ODEV2

Open Data Events (ODE) is about open-data calendars and public events.

It consists in a CalDAV server endpoint, a full website, and a REST API.

ODE is created to propose an alternative to closed-source, closed-data or vendor locking online calendars services. It allows everyone to maintain its own calendar service, to import, expose and share events information.

__Demo__ : [projet-ode.org](http://projet-ode.org/)

## License, Copyright & Contributeurs

(c) 2015 LiberTIC

Licence: [MIT (X11)](http://en.wikipedia.org/wiki/MIT_License)

Made in Nantes, France @ [Les Polypodes](http://lespolypodes.com)

[Contributors](https://github.com/LiberTIC/ODEV2/graphs/contributors)

## Requirements

- PHP >= 5.4.4
- PostgreSQL 9.2 with [JSON type support](http://www.postgresql.org/docs/9.2/static/datatype-json.html)
- PostgreSQL's [ext-pgsql PHP extension](http://php.net/manual/pgsql.installation.php)
- [composer](https://getcomposer.org/doc/00-intro.md#globally) installed and available in your PATH

### Resources

RAM: PHP's package manager, `composer`, internally increases the memory_limit to 1G. To get the current memory_limit value, run:

```bash
php -r "echo ini_get('memory_limit').PHP_EOL;"
```

If `composer` shows memory errors on some commands [check out this documentation](https://getcomposer.org/doc/articles/troubleshooting.md#memory-limit-errors).

## Installation, option 1: the Docker way

The `docker/docker-compose.yml` file already configure and install a working stack: Nginx web server, PHP-FPM, Postgresql, etc. A dedicated `Makefile` run the all needed operations. 

Make sure your [Docker](https://www.docker.com) local installation is OK, with both `docker` and `docker-compose` available commands, then call the `docker/Makefile` tasks:

```bash
cd docker
make               <-- build containers from images
make install       <-- database init (to be run once only) + run
```

That's it.

Once database is OK, next containers reboots only require this:

```bash
make run           <-- = docker-compose -up -d
```

## Installation, option 2: Using the Makefile

The install process warns you about requirements and configuration of Apache2, PHP5, PostgreSQL, etc.: Make sure you they are already available and running in you environment. 

Mac OS X users, you may want to install PostgreSQL easely, using [postgresApp](http://postgresapp.com/)

```bash
git clone https://github.com/LiberTIC/ODEV2.git
cd ODEV2
make
make install
```

Locally, you may then use the PHP Built-in web server to run ODE:

```bash
php app/console server:run
```

And then `open` (OS X) or `xdg-open` (GNU/Linux) the working URL: http://127.0.0.1:8000

## Installation for production environment

You can find more informations (Apache2 vhost configuration example, etc.) on how to go live [here](doc/GoingLive.md)

## Database daily tasks

The `Makefile` is to be used in your (local) webserver environment. Note that a D special, Docker-related `Makefile` also exists in the `docker` folder. 

Database init: `make install` already performs the role and database creation through SQL scripts and initial data: __you do not need to run these commands below at first install__. By the way, all PostgreSQL related Makefile commands are explicit, in order to let you tweak them, to fit better with your environment.

Available PostgreSQL related commands:

```
make createDb       # Creates PostgreSQL database ODE
make pgCreateRole   # Creates ODE role using doc/postgresql/role.sql
make pgInit         # Initializes ODE db using doc/pstgresql/init.dump
make pgDump         # Creates a restorable snapshot of ODE database in ./dumps
make pgRestore      # Uses the last restorable snapshot to restore ODE db
make dropDb         # Drops last db (it always runs pgDump before droping)
make resetDb        # Drops and re-init ODE db
make connect        # Connects you to psql 
```

## Quality & tests

```bash
make quality
```

## Documentation

### PHP code documentation

via [phpdoc](http://phpdoc.org/), to be read in a web browser

```bash
make phpdoc
open|xdg-open api/doc/index.html
```

### REST API documentation 

via [NelmioApiDocBundle](https://github.com/nelmio/NelmioApiDocBundle),  be read in a web browser

```bash
php app/console server:run
open|xdg-open http://127.0.0.1:8000/api/doc
```

Note that the REST API documentation provides a sandbox mode in order to test all provided API methods.

## v1 Analyzis & Memo

The `./doc` conserve (french) documentation & analyse about "v1" ODE project and "v2" prototype, included more 2015 meeting notes. 

## [TODO list](doc/TODO_list.md)

## Useful Links

- (fr) [Présentation du projet initial](http://fr.slideshare.net/libertic/lancement-projet-ode-culture) (slideshare.net)
- (fr) [Plan de travail de la journée de juin 2014 organisée à Stereolux](http://www.stereolux.org/labo-arts-techs/ouverture-des-donnees-evenementielles-lancement-officiel-du-site-10-06-2014)
- (fr) [v1 source code](https://github.com/LiberTIC/ODE): Python, Dango, Pyramid
- [v2 prototype source code](https://github.com/polypodes/CalDAVClientPrototype), based on Symfony2 + Apple CalendarServer
- [jquery-oembed-all source code](https://github.com/nfl/jquery-oembed-all)

## Let's chat!

[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/LiberTIC/ODEV2?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)


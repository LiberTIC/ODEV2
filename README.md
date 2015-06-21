# ODEV2

## Requirements

- PHP >= 5.4.4
- ext-pgsql PHP extension, for PostgreSQL
- PHP's package manager `Composer` internally increases the memory_limit to 1G

## ODE Installation:

Make sure [composer](https://getcomposer.org/doc/00-intro.md#globally), is installed and present in your PATH

Make sure to have a working instance of [PostgreSQL](http://www.postgresql.org/). Mac OS X users, you may want to install it easely using [postgresApp](http://postgresapp.com/).

Then use a terminal and type this commands:

```bash
git clone https://github.com/LiberTIC/ODEV2.git
cd ODEV2
# The install process warns you
# about requirements and configuration of Apache2, PHP5, PostgreSQL, etc.
make
make install
php app/console server:run
```

Then open http://127.0.0.1:8000

An Apache2 vhost configuration file is given in example in `./doc`

## Database tasks (included in the `make install`)

`make install` already performs the role and database creation through SQL scripts and initial data: __you do not need to run these commands at first install__. By the way, all PostgreSQL related Makefile commands are explicit, in order to let you tweak them, to fit better with your environment.

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

## Installation for production environment

You can find more information on how to go live [here](doc/GoingLive.md)

## Use of REST API

You can find more information about the API [here](doc/RestAPI.md)

Or you can just connect to http://{yourwebsite}/api/doc in order to check the full API documentation

## Quality & tests

```bash
make quality
```

## Code documentation (phpdoc)

```bash
make phpdoc
open api/doc/index.html
```


## License, Copyright & Contributeurs

(c) 2015 LiberTIC

Licence: [MIT (X11)](http://en.wikipedia.org/wiki/MIT_License)

Made in Nantes, France @ [Les Polypodes](http://lespolypodes.com)

[Contributeurs](https://github.com/LiberTIC/ODEV2/graphs/contributors)

## Analyse du projet Open Data Events v1

Ce dépôt conserve dans le répertoire `./doc` les documents et l'analyse de la v1 et ceux du prototype de la v2, ainsi que les compte-rendus des réunions de janvier à juin 2015 

## [TODO list](doc/TODO_list.md)

## Liens utiles

- [Présentation du projet initial](http://fr.slideshare.net/libertic/lancement-projet-ode-culture) (slideshare.net)
- [Plan de travail de la journée de juin 2014 organisée à Stereolux](http://www.stereolux.org/labo-arts-techs/ouverture-des-donnees-evenementielles-lancement-officiel-du-site-10-06-2014)
- [sources de la v1](https://github.com/LiberTIC/ODE): Python, Dango, Pyramid
- [prototype de la v2](https://github.com/polypodes/CalDAVClientPrototype): Symfony2, CalendarServer
- [Sources de jquery-oembed-all](https://github.com/nfl/jquery-oembed-all)

## Discutons !

[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/LiberTIC/ODEV2?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)


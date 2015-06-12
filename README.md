# ODEV2

###/!\ Les documents ont été déplacé dans le dossier /doc ! /!\


## Installation (development):

```bash
git clone https://github.com/LiberTIC/ODEV2.git
cd ODEV2
# The install process warns you
# about requirements and configuration of Apache2, MySQL & PHP5.
make
make install
php app/console server:run
```

## Installation (production):

You can find more information on how to go live [here](doc/GoingLive.md)

## Use of REST API

You can find more information about the API [here](doc/RestAPI.md)

## Qualité & tests

```bash
make quality
make tests
```

## License, Copyright & Contributeurs

(c) 2015 LiberTIC

Licence: [MIT (X11)](http://en.wikipedia.org/wiki/MIT_License)

Made in Nantes, France @ [Les Polypodes](http://lespolypodes.com)

[Contributeurs](https://github.com/LiberTIC/ODEV2/graphs/contributors)

## Analyse du projet Open Data Events

Ce dépôt conserve les documents et l'analyse de la v1 et ceux du prototype de la v2, ainsi que les compte-rendus des réunions de janvier-février 2015 

## Liens utiles

- [Présentation du projet initial](http://fr.slideshare.net/libertic/lancement-projet-ode-culture) (slideshare.net)
- [Plan de travail de la journée de juin 2014 organisée à Stereolux](http://www.stereolux.org/labo-arts-techs/ouverture-des-donnees-evenementielles-lancement-officiel-du-site-10-06-2014)
- [sources de la v1](https://github.com/LiberTIC/ODE): Python, Dango, Pyramid
- [source du prototype de la v2](https://github.com/polypodes/CalDAVClientPrototype): Symfony2, CalendarServer 
- [Sources de jquery-oembed-all](https://github.com/nfl/jquery-oembed-all)

## Discutons !

[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/LiberTIC/ODEV2?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)


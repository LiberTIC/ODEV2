Tutoriel: Comprendre CalDAV et l'appliquer
==========================================

**Work In Progress, revenez plus tard!**


#### Table Of Content

1. [CalDAV, kézaco ?](#1-caldav-k%C3%A9zaco-)
  * [Le protocol HTTP](#le-protocol-http)
  * [Le protocol WebDAV](#le-protocol-webdav)
  * [Le format iCalendar](#le-format-icalendar)
  * [Le protocol CalDAV](#le-protocol-caldav)
2. [Comment parler le iCalendar simple](#2-comment-parler-le-icalendar-simple)
  * [Un début et une fin](#un-d%C3%A9but-et-une-fin)
  * [ProdID](#prodid)
  * [Structure de l'Event](#structure-de-levent)
  * [Données utiles](#donn%C3%A9es-utiles)
  * [Exemple](#exemple)
3. [La récurrence](#3-la-r%C3%A9currence)




1) CalDAV, kézaco ?
-------------------

#### Le protocol HTTP

Tout part du protocol **HTTP**. Ce protocol est utilisé pour communiquer sur le réseau Internet. Il est défini par la [RFC 2616](http://tools.ietf.org/html/rfc2616). Même si ce protocol est suffisant pour échanger un grand nombre d'information et de données, il était important de rajouter des spécifications pour certaines utilisations de ce protocol. C'est pourquoi une RFC suivante à introduit **WebDAV**.


#### Le protocol WebDAV

**WebDAV** est une extension du protocol **HTTP**, définie par la [RFC 4918](http://tools.ietf.org/html/rfc4918). Elle est utilisé dans le but d'échanger des fichiers et dossier facilement sur le réseau internet. On peut comparer **WebDAV** à **FTP** (se basant sur le protocol **IP**) ou à **SFTP** (se basant sur le protocol **SSH**).


#### Le format iCalendar

**iCalendar** est un format de fichier (.ical ; .ics ; .ifb ; .icalendar). Il permet de stocker des événements (tels que "Cinéma à 16h" ou "Réunion service compta' tous les lundi à 9h"). Ce format est ensuite utilisé par des logiciels (tels que iCal, Google Calendar, etc.. ) pour en faire un affichage plus "user-friendly".

> Et où ça nous mène tout ça ?


#### Le protocol CalDAV

**CalDAV** est une extension de **WebDAV**. En effet, **CalDAV** précise la façon d'envover des informations au format **iCalendar** sur le réseau Internet. Elle est définie par la RFC [4792](http://tools.ietf.org/html/rfc4791).


**En résumé:** **CalDAV** permet d'échanger des fichiers au format **iCalendar** sur le réseau Internet grâce à **HTTP** et son extension **WebDAV**

*Pour plus d'info sur les protocols et formats utilisé: [Lexique](https://github.com/LiberTIC/ODEV2/blob/master/Thibaud_Printemps2015/lexique.md)



2) Comment parler le iCalendar simple
-------------------------------------

#### Un début et une fin

Tout fichier sous le format **iCalendar** doit suivre un certain formatage des données

En effet, tout fichier doit commencer par `BEGIN:VCALENDAR` et doit finir `END:VCALENDAR`.

La seconde ligne doit spécifier la version du format. iCalendar est actuellement en version 2.0, nous utiliseront donc `VERSION:2.0`

Voici donc à quoi ressemble la plupart des fichier iCalendar:

```
BEGIN:VCALENDAR
VERSION:2.0

[...]

END:VCALENDAR
```

(Un vrai fichier ne doit pas contenir de ligne vide sinon certains clients, tel que Google Calendar, refuseront le fichier.)

#### ProdID

Après le numéro de version, il faut indiquer un **PRODID**. Il s'agit des détails de l'application/société. Il est souvent sous la forme:

`PRODID:-//{Business Name}//{Product/App Name}//{Language}`

En l'ajoutant au reste, on peut obtenir quelque chose comme ceci:

```
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Polypodes//CalInterne//FR

[...]

END:VCALENDAR
```

#### Structure de l'Event

Après avoir construit la structure du fichier, il faut lui ajouter des objets. Il peut y avoir plusieurs type d'objets: **vTODO**, **vJOURNAL** ou **vEVENT**. C'est ce dernier qui nous intéresse.

Un objet **vEVENT** donc, commence par `BEGIN:VEVENT` et fini par `END:VEVENT`.

Un objet **vEVENT** doit posséder un IDentifiant Unique (**UID**). Plusieurs objets possèdant le même **UID** référence le même événement logique. (Vous comprendrez par la suite)

Un objet **vEVENT** **peut** avoir un champ **Status** pouvant avoir comme valeur "CONFIRMED", "TENTATIVE" ou "CANCELLED", mais il est optionnel (valeur par défaut: CONFIRMED). Ex: `STATUS:CANCELLED`

Un objet **vEVENT** doit renseigner l'heure et la date à laquelle il a été créé. Il s'agit du champs "DTSTAMP" au format UTC. Ex: `DTSTAMP:20150421T090945Z` défini le 21 avril 2015 à 11 heures 9 et 45 secondes à Europe/Paris (UTC+2).

#### Données utiles

Un objet **vEVENT** ajoute un résumé de l'événement. Il est défini par la ligne `SUMMARY:{Le résumé de l'événement}`. Il s'agit de ce qui sera affiché dans le client.

**DTSTART, DTEND** définissent le début et la fin de l'évènement au format UTC (cf. plus haut).

Si l'événement à été modifié, il faut ajouter le champs **LAST-MODIFIED** au format UTC.

Il est possible d'ajouter un lieu à l'événement grâce au champs **LOCATION**.


#### Exemple

Voici donc un exemple d'un fichier iCalendar décrivant une réunion du service comptabilité:
```
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Polypodes//CalInterne//FR

	BEGIN:VEVENT
	UID:123456789
	DTSTAMP:20150421T090945Z

		SUMMARY:Réunion service comptabilité
		DTSTART:20150427T080000Z
		DTEND:20150427T090000Z
		LOCATION:Salle de réunion D

	END:VEVENT

END:VCALENDAR
```

(Rappel: Il ne faut pas employer de tabulation ni de ligne vide dans un vrai fichier iCalendar)


3) La récurrence
----------------

Les objets **vEVENT** peuvent ajouter une règle de récurrence. Par exemple, si on veut que notre réunion du service compta soit une 

#### Récurrence simple

Il est possible 
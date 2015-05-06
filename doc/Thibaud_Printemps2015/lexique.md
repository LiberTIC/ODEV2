Lexique
=======

Calendar et autre
-----------------

### Protocols

#### WebDAV

**Web Distributed Authoring and Versioning** (ou **WebDAV**) est une extension du protocole **HTTP**. Il permet de récupérer, déposer, synchronier et publier des fichiers rapidement et facilement. WebDAV permet une édition de contenu simultanée avec plusieurs utilisateurs. WebDAV est décrit dans la [RFC 4918](http://tools.ietf.org/html/rfc4918).

*Source :* [Wikipedia - WebDAV](http://en.wikipedia.org/wiki/WebDAV)


#### CalDAV

**Calendar Extensions to WebDAV** (ou **CalDAV**) est un standard internet permettant à un client de planifier des informations sur un serveur distant. Il étend les spécifications de **WebDAV** et utilise le format **iCalendar** pour les données. CalDAV est décrit dans la [RFC 4791](http://tools.ietf.org/html/rfc4791).

*Source :* [Wikipedia - CalDAV](http://en.wikipedia.org/wiki/CalDAV)


#### CardDAV

**vCard Extensions to WebDAV** (ou **CardDAV**) est un standard internet permettant à un client d'accéder et de partager des données de contacts sur un serveur disant. Il étend les spécifications de **WebDAV** et utilise le format **vCard** pour les données. **CardDAV** est décrit dans le [RFC 6352](http://tools.ietf.org/html/rfc6352).

*Source :* [Wikipedia - CardDAV](http://en.wikipedia.org/wiki/CardDAV)


### Formats de fichiers


#### iCalendar

**iCalendar** est un format de fichier (.ical ; .ics ; .ifb ; .icalendar) permettant d'envoyer des événements entre utilisateur. iCalendar est indépendant du protocol de transport. En effet, le transport eut être effectué par mail, par partage sur un serveur **WebDAV** ou même par pigeon voyageur.

*Source :* [Wikipedia - iCalendar](http://en.wikipedia.org/wiki/ICalendar)


#### vCard

**vCard Extensions to WebDAV** (ou **CardDAV**) est un format de fichier (.vcf ; .vcard) permettant de de transmettre des données personnelles (sous forme de Carte de visite). Le format vCal peut être utilisé en parallèle avec le format **iCalendar** pour lier des événements à des personnes. vCard est décrit dans la [RFC 6350](http://tools.ietf.org/html/rfc6350). La version actuelle est la 4.0, mais la 3.0 reste utilisé par de nombreux clients et serveurs.

*Source :* [Wikipedia - vCard](http://en.wikipedia.org/wiki/VCard)


#### hCalendar

**HTML iCalendar** (ou **hCalendar**) est un microformat pour afficher une représentation HTML sémantique d'un calendrier au format iCalendar. L'avantage, outre un affichage personnalisé des événements, est la possibilité données à des outils de parsing d'extraire les informations pour les stocker sous le format souhaité (iCalendar ou autre). hCalendar est utilisé entre autres par Facebook, Google et Wikipédia.

*Source :* [Wikipedia - hCalendar](http://en.wikipedia.org/wiki/HCalendar)


### Clients


#### iCal

**iCal** (renommé **Calendar** depuis OSX Mountain Lion) est l'application de gestion de calendrier fait par Apple. Il peux, entre autres, être client de d'un serveur **WebDAV**.

*Source :* [Wikipedia - Calendar](http://en.wikipedia.org/wiki/Calendar_%28application%29)


### Serveurs


#### SabreDAV

**SabreDAV** est un serveur **CardDAV**, **CalDAV** et **WebDAV**. Il implémente les recommendations RFC actuelles. Il est compatible avec toutes les plateformes majeures.

*Source :* [Wikipedia - SabreDAV](http://en.wikipedia.org/wiki/SabreDAV)


#### CalServ

**Calendar and Contacts Server**, aussi appelé **Calendar Server** ou **CalServ** est un projet d'Apple d'implémentation des protocols CalDAV et CardDAV. Sortie en 2006 sous le nom de iCal Server and Address Book Server, il a été porté sur des plateformes non-Apple. Il est écrit en Python et utilise une base de données SQL pour stocker les données.

*Source :* [Wikipedia - Calendar Server](http://en.wikipedia.org/wiki/Calendar_and_Contacts_Server)


### Mix


#### Baïkal

**Baïkal** est une surcouche de **SabreDAV**. Il propose entre autres une interface d'administration web permettant la gestion du serveur CalDAV et CardDAV. Il est donc serveur et client de son propre serveur.

*Source :* [Site Baïkal](http://baikal-server.com/)
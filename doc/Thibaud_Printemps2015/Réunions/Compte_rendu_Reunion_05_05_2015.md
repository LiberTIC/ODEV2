

####CalDAV

* Il a été décidé d'utiliser un serveur CalDAV pour gérer les événements des utilisateurs. 

####Format et champs

* Tous les champs seront stocké dans un .ics, soit grâce aux champs natif iCalendar, soit avec les champs non standard préfixé par "X-"
* Tous les champs seront aussi accessible avec une URL qui proposera les données dans un autre format (probablement JSON)
* On retire les champs Ville et Pays, le champ lieu étant suffisant. (Pour le géocoding: [Bano](http://openstreetmap.fr/bano) est une possibilité)
* Il faut cependant ajouter un champs pour préciser une salle (ou autre) à l'intérieur de ce lieu (Ex: 3ème porte à gauche au deuxième étage de l'immeuble n°1) (A voir dans la RFC CalDAV)
* La gestion des horaires d'ouverture, cela ne sera pas implémenté dans la première version de la V2 et il faudra créer autant d'événements que nécéssaire.

####Médias

* La gestion des médias se fera grâce au format [oEmbed](http://www.oembed.com/)





##Pour la prochaine réunion

Pour la prochaine réunion, un prototype devra être montré avec:

* Un serveur CalDAV opérationnel pour ajouter/modifier/supprimer des événements et des calendriers.
* Une première version d'un front pour se connecter avec les mêmes identifiants
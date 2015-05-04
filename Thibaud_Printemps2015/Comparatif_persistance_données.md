Comparatif de CalDAV, SQL et Elastic Search pour la persistance des données
===========================================================================


*Note 1: Ce comparatif est un Work In Progress.*

*Note 2: iCalendar est indépendant de CalDAV. Donc l'abonnement à un calendrier est possible sans utiliser CalDAV*


### CalDAV


**Avantages:**

* Permet une utilisation par les clients iCalendar (Google Calendar, iCal (Apple), etc...)
* Permet une gestion de la récurrence


**Inconvénients:**

* Besoin d'une indexation des champs supplémentaire (X-ODE-...)
* Besoin de modifier le processus d'indexation à chaque nouveau champs
* Gestion complexes des utilisateurs


### SQL


**Avantages:**

* Permet une gestion facilité des requêtes
* Permet une dissociation des objets (Ex: Un objet organisateur, un objet groupe de musique, etc...)
* Simplification de la modification des événements
* Gestion simple de la transformation SQL <=> (iCalendar ou XML ou JSON ou CSV)
* Permet de faire des liens entre les utilisateurs du site et les organisateurs des événements


**Inconvénients:**

* Ne permet pas une utilisation par les clients iCalendar (Google Calendar, iCal (Apple), etc...
* Ne permet pas une gestion de la récurrence


### Elastic Search

**Avantages:**

* Permet une gestion ultra simple du stockage (de l'indexation) des événements
* Permet une gestion ultra simple de la recherche des événements
* Possède un suivi statistique intégré puissant et adapté
* Possède une API REST intégré facilement utilisable
* Stockage déjà disponible sous format JSON ( donc facilement transformable vers CSV, iCalendar et XML )

**Inconvénients:**

* Ne permet pas une utilisation par les clients iCalendar (Google Calendar, iCal (Apple), etc...
* Ne permet pas une gestion de la récurrence

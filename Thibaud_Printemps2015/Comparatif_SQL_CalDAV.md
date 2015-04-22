Comparatif de SQL et CalDAV pour persister les événements
============================================================


*Note 1: Ce comparatif est un Work In Progress.*


### CalDAV


**Avantages:**

* Permet une utilisation par les clients iCalendar (Google Calendar, iCal (Apple), etc...)


**Inconvénients:**

* Besoin d'une indexation des champs supplémentaire (X-ODE-...)
* Besoin de modifier le processus d'indexation à chaque nouveau champs
* Gestion complexes des utilisateurs


### Serveur SQL


**Avantages:**

* Permet un gestion facilité des requêtes
* Permet une dissociation des objets (Ex: Un objet organisateur, un objet groupe de musique, etc...)
* Simplification de la modification des événements
* Gestion simple de la transformation SQL <=> (iCalendar ou XML ou JSON ou CSV)


**Inconvénients:**

* 
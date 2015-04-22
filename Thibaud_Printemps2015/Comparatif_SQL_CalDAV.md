Comparatif de SQL et CalDAV pour persister les événements
============================================================


*Note 1: Ce comparatif est un Work In Progress.*


### Serveur CalDAV


**Avantages:**

* Permet une utilisation par les clients iCalendar (Google Calendar, iCal (Apple), etc...)
* Permet une gestion de la récurrence


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
* Permet de faire des liens entre les utilisateurs du site et les organisateurs des événements


**Inconvénients:**

* Ne permet pas une gestion de la récurrence




*Note 2: La récurrence n'est utile que dans très peu de cas. Elle est surtout utile pour indiquer les horaires des musées ou des événements sur long terme. Or je pense que les événements unique (ou presque) tels qu'un concert de musique ou une conférence, sont à différencier des événements qui sont sur du long terme tels qu'une exposition, un musée, etc...*

*De plus, sachant que la récurrence tels que décrit par la [RFC de iCalendar](https://tools.ietf.org/html/rfc5545) est plutôt compliqué, les utilisateurs seront perdu et ajouteront de mauvaises règles, ce qui les perturbera, ainsi que ceux qui récupéreront les événements*
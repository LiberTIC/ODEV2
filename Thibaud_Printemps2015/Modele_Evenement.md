Modèle d'un Événement
=====================

*Note 1: Ceci est une* **proposition** *. Chaque propriété* **non indispensable** *peut être débattu.*

*Note 2: Les nom iCalendar commençant par "X-" ne sont pas définitifs*

*Note 3: Le suffixe "X-" sera remplacé par "X-ODE-"*

*Note 4: Activez le mode raw si vous n'arrivez pas à voir le tableau en entier*


| Propriété        | Nom iCalendar    | Type     | Description                    | Source     | Avis préliminaire | Exemple                  |
|:-----------------|:-----------------|:--------:|:-------------------------------|:-----------|:------------------|:-------------------------|
| **Nom et descr.**|                  |          |                                |            |                   |                          |
| Nom              | SUMMARY          | Texte    | Nom court de l'événement       | iCalendar  | **Indispensable** | Concert Shakaponk        |
| UID              | UID              | Nombre   | Identifiant Unique de l'event  | iCalendar  | **Indispensable** | SL-2015-XYZ-004          |
| Description      | DESCRIPTION      | Texte    | Description de l'événement     | iCalendar  | Important         | Concert de rock et [...] |
|                  |                  |          |                                |            |                   |                          |
| **Date et heure**|                  |          |                                |            |                   |                          |
|                  |                  |          |                                |            |                   |                          |
| Date début       | DTSTART          | Date     | Date et heure de début         | iCalendar  | **Indispensable** | 2014-06-20 / 20:00       |
| Date fin         | DTEND            | Date     | Date et heure de fin           | iCalendar  | **Indispensable** | 2014-06-20 / 23:30       |
|                  |                  |          |                                |            |                   |                          |
| **Localisation** [\[1\]](#liste-de-points-%C3%A0-d%C3%A9battre)              ||||            |                   |                          |
|                  |                  |          |                                |            |                   |                          |
| Lieu             | LOCATION         | Texte    | Nom de l'endroit (pas geoloc)  | iCalendar  | Important         | Zénith Nantes            |
| Géolocalisation  | GEO              | Geo      | Géolocalisation de l'événement | iCalendar  | Très utile        | 47.229234, -1.628550     |
| Ville            | X-TOWN           | Texte    | Nom de la ville de l'event     | ODE_V1     | Utile             | Nantes                   |
| Pays             | X-COUNTRY        | Texte    | Nom du pays de l'event         | ODE_V1     | Peu utile         | France                   |
| Capacité du lieu | X-LOC-CAPACITY   | Nombre   | Capacité du lieu de l'event    | ODE_V1     | Utile             | 4000 (personnes)         |
|                  |                  |          |                                |            |                   |                          |
| **Organisation** |                  |          |                                |            |                   |                          |
|                  |                  |          |                                |            |                   |                          |
| Participants     | X-ATTENDEES      | Texte    | Participants à l'événement     | schema.org | Utile             | Shakaponk;Tagada Jones   |
| Durée            | X-DURATION       | Durée    | Durée de l'événement           | schema.org | Utile (Optionnel) | PT3H30M (3h30min)        |
| Status           | X-STATUS         | Texte    | Status de l'événement          | schema.org | Utile (Optionnel) | Annulé / Reporté         |
| Organisateur     | X-ORGANIZER      | Texte    | Organisateur de l'événement    | schema.org | Très utile        | Stéréolux                |
| Sous-Événement   | x-SUBEVENT       | UID      | UID d'un sous-événement        | schema.org | Utile (Optionnel) | SL-2015-XYZ-009          |
| Super-Événement  | x-SUPEREVENT     | UID      | UID d'un sur-événement         | schema.org | Utile (Optionnel) | SL-2015-XYZ-001          |
|                  |                  |          |                                |            |                   |                          |
| **Médias** [\[3\]](#liste-de-points-%C3%A0-d%C3%A9battre)                    ||||            |                   |                          |
|                  |                  |          |                                |            |                   |                          |
| Image            | X-IMAGE          | URL      | Url d'une image de l'événement | schema.org | Très utile        | http://website/image.jpg |
| URL              | X-URL            | URL      | URL sur le site organisateur   | schema.org | Important         | http://website/concert/  |
|                  |                  |          |                                |            |                   |                          |
| **International**|                  |          |                                |            |                   |                          |
|                  |                  |          |                                |            |                   |                          |
| Langue           | X-LANGUAGE       | Texte    | Langue de l'événement          | ODE_V1     | Utile             | FR (Français)            |
|                  |                  |          |                                |            |                   |                          |
| **Tarifs**       |                  |          |                                |            |                   |                          |
|                  |                  |          |                                |            |                   |                          |
| Prix standard    | X-PRICE-STANDARD | Nombre   | Prix au tarif normal           | N\A        | Important         | 10 (10 €)                |
| Prix réduit      | X-PRICE-REDUCED  | Nombre   | Prix au tarif réduit           | N\A        | Important (Opt.)  | 7.5 (7.5 €)              |
| Prix enfant      | X-PRICE-CHILDREN | Nombre   | Prix au tarif enfant           | N\A        | Important (Opt.)  | 5 (5 €)                  |
|                  |                  |          |                                |            |                   |                          |
| **Contacts** [\[2\]](#liste-de-points-%C3%A0-d%C3%A9battre)                  ||||            |                   |                          |
|                  |                  |          |                                |            |                   |                          |
| Contact - Nom    | X-CONTACT-NAME   | Texte    | Nom du contact                 | ODE_V1     | Utile             | John Smith               |
| Contact - Email  | X-CONTACT-EMAIL  | Email    | Email du contact               | ODE_V1     | Utile             | john.smith@email.com     |
|                  |                  |          |                                |            |                   |                          |
|**Catégorisation**|                  |          |                                |            |                   |                          |
|                  |                  |          |                                |            |                   |                          |
| Catégorie        | X-CATEGORY       | Texte    | Catégorie de l'événement       | ODE_V1     | Important         | Concert                  |
| Tags             | X-TAGS           | Texte    | Tags de l'événement            | ODE_V1     | Important         | Rock;Alternatif;[...]    |


#### Liste de points à débattre:
* \[1\]: Géolocalisation ou Addresse
* \[2\]: Ajout contact presse, ajout contact ticket ?
* \[3\]: Vidéos, Sons ? Plus d'images ? Droit d'auteur sur les médias ?


#### Sources:
* [RFC iCalendar](https://tools.ietf.org/html/rfc5545)
* [schema.org](http://schema.org/Event)
* [ODE V1](https://github.com/LiberTIC/ODE)
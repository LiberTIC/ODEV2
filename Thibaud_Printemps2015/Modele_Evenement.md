Modèle d'un Événement
=====================

*Note 1: Ceci est une proposition. Chaque propriété* **non indispensable** *peut être débattu.*

*Note 2: Les nom iCalendar commençant par "X-" ne sont pas définitifs*

*Note 3: Le suffixe "X-" sera remplacé par "X-ODE-"*

*Note 4: Activez le mode raw si vous n'arrivez pas à voir le tableau en entier*


| Propriété       | Nom iCalendar    | Type     | Description                    | Source     | Avis              | Exemple                  |
|:----------------|:-----------------|:--------:|:-------------------------------|:-----------|:------------------|:-------------------------|
| Nom             | SUMMARY          | Texte    | Nom de l'événement             | iCalendar  | **Indispensable** | Concert Shakaponk        |
| UID             | UID              | Nombre   | Identifiant Unique de l'event  | iCalendar  | **Indispensable** | SL-2015-XYZ-004          |
| Date début      | DTSTART          | Date     | Date et heure de début         | iCalendar  | **Indispensable** | 2014-06-20 / 20:00       |
| Date fin        | DTEND            | Date     | Date et heure de fin           | iCalendar  | **Indispensable** | 2014-06-20 / 23:30       |
| Lieu            | LOCATION         | Texte    | Nom de l'endroit (pas geoloc)  | iCalendar  | Important         | Zénith Nantes            |
| Description     | DESCRIPTION      | Texte    | Description de l'événement     | iCalendar  | Important         | [...]                    |
| Geolocalisation | GEO              | Geo      | Géolocalisation de l'événement | iCalendar  | Très utile        | 47.229234, -1.628550     |
|                 |                  |          |                                |            |                   |                          |
| Participants    | X-ATTENDEES      | Texte    | Participants à l'événements    | schema.org | Utile             | Shakaponk, Tagada Jones  |
| Durée           | X-DURATION       | Durée    | Durée de l'événement           | schema.org | Utile (Optionnel) | PT3H30M (3h30min)        |
| Status          | X-STATUS         | Texte    | Status de l'événement          | schema.org | Utile (Optionnel) | Annulé / Reporté         |
| Organisateur    | X-ORGANIZER      | Texte    | Organisateur de l'événement    | schema.org | Très utile        | Stéréolux                |
| Sous-Événement  | x-SUBEVENT       | UID      | UID d'un sous-événement        | schema.org | Utile (Optionnel) | SL-2015-XYZ-009          |
| Super-Événement | x-SUPEREVENT     | UID      | UID d'un sur-événement         | schema.org | Utile (Optionnel) | SL-2015-XYZ-001          |
|                 |                  |          |                                |            |                   |                          |
| Image           | X-IMAGE          | URL      | Url d'une image de l'événement | schema.org | Très utile        | http://website/image.jpg |
| URL             | X-URL            | URL      | URL sur le site organisateur   | schema.org | Important         | http://website/concert/  |
|                 |                  |          |                                |            |                   |                          |
| Langue          | X-LANGUAGE       | Texte    | Langue de l'événement          | ODE_V1     | Utile             | FR (Français)            |
|                 |                  |          |                                |            |                   |                          |
| Prix standard   | X-PRICE-STANDARD | Nombre   | Prix au tarif normal           | N\A        | Important         | 10 (10 euros)            |
| Prix réduit     | X-PRICE-REDUCED  | Nombre   | Prix au tarif réduit           | N\A        | Important (Opt.)  | 7.5 (7.5 euros)          |
| Prix enfant     | X-PRICE-CHILDREN | Nombre   | Prix au tarif enfant           | N\A        | Important (Opt.)  | 5 (5 euros)              |


(Idées en vrac: contact/ville/categories/tags)

Sources:
* [RFC iCalendar](https://tools.ietf.org/html/rfc5545)
* [schema.org](http://schema.org/Event)
* [ODE V1](https://github.com/LiberTIC/ODE)
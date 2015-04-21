Modèle d'un Événement
=====================

*Note 1: Ceci est une proposition. Chaque propriété* **non indispensable** *peut être débattu.*

*Note 2: Les nom iCalendar commençant par "X-" ne sont pas définitifs*


| Propriété       | Nom iCalendar | Type  | Description                    | Source     | Avis                | Exemple                 |
|:----------------|:--------------|:-----:|:-------------------------------|:-----------|:--------------------|------------------------:|
| Nom             | SUMMARY       | Texte | Nom de l'événement             | iCalendar  | **Indispensable**   | Concert Shakaponk       |
| Date début      | DTSTART       | Date  | Date et heure de début         | iCalendar  | **Indispensable**   | 2014-06-20 / 20:00      |
| Date fin        | DTEND         | Date  | Date et heure de fin           | iCalendar  | **Indispensable**   | 2014-06-20 / 23:30      |
| Lieu            | LOCATION      | Texte | Nom de l'endroit (pas geoloc)  | iCalendar  | Important           | Zénith Nantes           |
| Description     | DESCRIPTION   | Texte | Description de l'événement     | iCalendar  | Important           | [...]                   |
| Geolocalisation | GEO           | Geo   | Géolocalisation de l'événement | iCalendar  | Très utile          | 47.229234, -1.628550    |
|                 |               |       |                                |            |                     |                         |
| Participants    | X-ATTENDEES   | Texte | Participants à l'événements    | schema.org | Utile               | Shakaponk, Tagada Jones |
| Organisateur    | X-ORGANIZER   | Texte | Organisateur de l'événement    | schema.org | Très utile          | Stéréolux               |
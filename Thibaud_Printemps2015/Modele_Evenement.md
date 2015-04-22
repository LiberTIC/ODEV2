Modèle d'un Événement
=====================

*Note 1: Ceci est une* **proposition** *. Chaque propriété* **non indispensable** *peut être débattu.*

*Note 2: Les nom iCalendar commençant par "X-" ne sont pas définitifs*

*Note 3: Le suffixe "X-" sera remplacé par "X-ODE-"*

*Note 4: Activez le mode raw si vous n'arrivez pas à voir le tableau en entier*


| Propriété        | Type     | Description                    | Source     | Avis préliminaire | Exemple                  |
|:-----------------|:--------:|:-------------------------------|:-----------|:------------------|:-------------------------|
| **Nom et descr.**|          |                                |            |                   |                          |
| Nom              | Texte    | Nom court de l'événement       | iCalendar  | **Indispensable** | Concert Shakaponk        |
| UID              | Nombre   | Identifiant Unique de l'event  | iCalendar  | **Indispensable** | SL-2015-XYZ-004          |
| Description      | Texte    | Description de l'événement     | iCalendar  | Important         | Concert de rock et [...] |
|                  |          |                                |            |                   |                          |
| **Date et heure**|          |                                |            |                   |                          |
|                  |          |                                |            |                   |                          |
| Date début       | Date     | Date et heure de début         | iCalendar  | **Indispensable** | 2014-06-20 / 20:00       |
| Date fin         | Date     | Date et heure de fin           | iCalendar  | **Indispensable** | 2014-06-20 / 23:30       |
|                  |          |                                |            |                   |                          |
| **Localisation** [\[1\]](#liste-de-points-%C3%A0-d%C3%A9battre)||||       |                   |                          |
|                  |          |                                |            |                   |                          |
| Lieu             | Texte    | Nom de l'endroit (pas geoloc)  | iCalendar  | Important         | Zénith Nantes            |
| Géolocalisation  | Geo      | Géolocalisation de l'événement | iCalendar  | Très utile        | 47.229234, -1.628550     |
| Ville            | Texte    | Nom de la ville de l'event     | ODE_V1     | Utile             | Nantes                   |
| Pays             | Texte    | Nom du pays de l'event         | ODE_V1     | Peu utile         | France                   |
| Capacité du lieu | Nombre   | Capacité du lieu de l'event    | ODE_V1     | Utile             | 4000 (personnes)         |
|                  |          |                                |            |                   |                          |
| **Organisation** |          |                                |            |                   |                          |
|                  |          |                                |            |                   |                          |
| Participants     | Texte    | Participants à l'événement     | schema.org | Utile             | Shakaponk;Tagada Jones   |
| Durée            | Durée    | Durée de l'événement           | schema.org | Utile (Optionnel) | PT3H30M (3h30min)        |
| Status           | Texte    | Status de l'événement          | schema.org | Utile (Optionnel) | Annulé / Reporté         |
| Organisateur     | Texte    | Organisateur de l'événement    | schema.org | Très utile        | Stéréolux                |
| Sous-Événement   | UID      | UID d'un sous-événement        | schema.org | Utile (Optionnel) | SL-2015-XYZ-009          |
| Super-Événement  | UID      | UID d'un sur-événement         | schema.org | Utile (Optionnel) | SL-2015-XYZ-001          |
|                  |          |                                |            |                   |                          |
| **Médias** [\[3\]](#liste-de-points-%C3%A0-d%C3%A9battre)||| |            |                   |                          |
|                  |          |                                |            |                   |                          |
| Image            | URL      | Url d'une image de l'événement | schema.org | Très utile        | http://website/image.jpg |
| URL              | URL      | URL sur le site organisateur   | schema.org | Important         | http://website/concert/  |
|                  |          |                                |            |                   |                          |
| **International**|          |                                |            |                   |                          |
|                  |          |                                |            |                   |                          |
| Langue           | Texte    | Langue de l'événement          | ODE_V1     | Utile             | FR (Français)            |
|                  |          |                                |            |                   |                          |
| **Tarifs**       |          |                                |            |                   |                          |
|                  |          |                                |            |                   |                          |
| Prix standard    | Nombre   | Prix au tarif normal           | N\A        | Important         | 10 (10 €)                |
| Prix réduit      | Nombre   | Prix au tarif réduit           | N\A        | Important (Opt.)  | 7.5 (7.5 €)              |
| Prix enfant      | Nombre   | Prix au tarif enfant           | N\A        | Important (Opt.)  | 5 (5 €)                  |
|                  |          |                                |            |                   |                          |
| **Contacts** [\[2\]](#liste-de-points-%C3%A0-d%C3%A9battre)||||           |                   |                          |
|                  |          |                                |            |                   |                          |
| Contact - Nom    | Texte    | Nom du contact                 | ODE_V1     | Utile             | John Smith               |
| Contact - Email  | Email    | Email du contact               | ODE_V1     | Utile             | john.smith@email.com     |
|                  |          |                                |            |                   |                          |
|**Catégorisation**|          |                                |            |                   |                          |
|                  |          |                                |            |                   |                          |
| Catégorie        | Texte    | Catégorie de l'événement       | ODE_V1     | Important         | Concert                  |
| Tags             | Texte    | Tags de l'événement            | ODE_V1     | Important         | Rock;Alternatif;[...]    |


#### Liste de points à débattre:
* \[1\]: Géolocalisation ou Addresse
* \[2\]: Ajout contact presse, ajout contact ticket ?
* \[3\]: Vidéos, Sons ? Plus d'images ? Droit d'auteur sur les médias ?


#### Sources:
* [RFC iCalendar](https://tools.ietf.org/html/rfc5545)
* [schema.org](http://schema.org/Event)
* [ODE V1](https://github.com/LiberTIC/ODE)




Définition technique des noms
-----------------------------

| Propriété         | iCalendar               | Json, CSV & XML        | Obligatoire |
|:------------------|:------------------------|:-----------------------|:-----------:|
| Nom               | SUMMARY                 | name                   | Oui         |
| UID               | UID                     | id                     |             |
| Description       | DESCRIPTION             | description            | Oui         |
| Date début        | DTSTART                 | date_start             | Oui         |
| Date fin          | DTEND                   | date_end               | Oui         |
| Lieu              | LOCATION                | location_name          |             |
| Géolocalisaion    | GEO                     | geo                    |             |
| Ville             | X-ODE-TOWN              | location_town          |             |
| Pays              | X-ODE-COUNTRY           | location_country       |             |
| Capacité du lieu  | X-ODE-LOCATION-CAPACITY | location_capacity      |             |
| Participants      | X-ODE-ATTENDEES         | attendees              |             |
| Durée             | X-ODE-DURATION          | duration               |             |
| Status            | X-ODE-STATUS            | status                 |             |
| Organisateur      | X-ODE-ORGANIZER         | organizer              | Oui         |
| Sous-Événement    | X-ODE-SUBEVENT          | subevent               |             |
| Super-Événement   | X-ODE-SUPEREVENT        | superevent             |             |
| Image             | X-ODE-IMAGE             | image                  |             |
| URL               | X-ODE-URL               | url                    |             |
| Langue            | X-ODE-LANGUAGE          | language               | Oui         |
| Prix standard     | X-ODE-PRICE-STANDARD    | price_standard         |             |
| Prix réduit       | X-ODE-PRICE-REDUCED     | price_reduced          |             |
| Prix enfant       | X-ODE-PRICE-CHILDREN    | price_children         |             |
| Contact - Nom     | X-ODE-CONTACT-NAME      | contact_name           |             |
| Contact - Email   | X-ODE-CONTACT-EMAIL     | contact_email          |             |
| Catégorie         | X-ODE-CATEGORY          | category               | Oui         |
| Tags              | X-ODE-TAGS              | tags                   |             |

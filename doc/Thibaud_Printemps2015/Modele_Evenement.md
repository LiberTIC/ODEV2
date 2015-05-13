Modèle d'un Événement
=====================

*Note 1: Ceci est une* **proposition** *. Chaque propriété* **non indispensable** *peut être débattu.*

*Note 2: Activez le mode raw si vous n'arrivez pas à voir le tableau en entier*

*Note 3: Les dates seront au format [ISO 8601](http://en.wikipedia.org/wiki/ISO_8601)*

*Note 4: Le champs UID sera généré par le serveur et ne sera pas à fournir par l'utilisateur*


| Propriété        | Type     | Description                           | Source     | Exemple                      |
|:-----------------|:--------:|:--------------------------------------|:-----------|:-----------------------------|
| **Nom et descr.**|          |                                       |            |                              |
| Nom              | Texte    | Nom court de l'événement              | iCalendar  | Concert Shakaponk            |
| UID              | Nombre   | Identifiant Unique de l'event         | iCalendar  | SL-2015-XYZ-004              |
| Description      | Texte    | Description de l'événement            | iCalendar  | Concert de rock et [...]     |
|                  |          |                                       |            |                              |
| **Date et heure**|          |                                       |            |                              |
|                  |          |                                       |            |                              |
| Date début       | Date     | Date et heure de début                | iCalendar  | 2015-06-20 / 20:00           |
| Date fin         | Date     | Date et heure de fin                  | iCalendar  | 2015-06-20 / 23:30           |
| Date création    | Date     | Date de création de l'event           | ODE_V2     | 2015-04-01 / 13:37           |
| Date modification| Date     | Date de modif de l'événement          | ODE_V2     | 2015-04-03 / 20:15           |
|                  |          |                                       |            |                              |
| **Localisation** |          |                                       |            |                              |
|                  |          |                                       |            |                              |
| Lieu             | Texte    | Nom de l'endroit                      | iCalendar  | Zénith Nantes                |
| Emplacement      | Texte    | Nom de l'endroit                      | ODE_V2     | Salle de concert n°3         |
| Géolocalisation  | Geo      | Géolocalisation de l'événement        | iCalendar  | 47.229234, -1.628550         |
| Capacité du lieu | Nombre   | Capacité du lieu de l'event           | ODE_V1     | 4000 (personnes)             |
|                  |          |                                       |            |                              |
| **Organisation** |          |                                       |            |                              |
|                  |          |                                       |            |                              |
| Participants     | Texte    | Participants à l'événement            | schema.org | Shakaponk;Tagada Jones       |
| Durée            | Durée    | Durée de l'événement                  | schema.org | PT3H30M (3h30min)            |
| Status           | Texte    | Status de l'événement                 | iCalendar  | Annulé / Reporté             |
| Organisateur     | Texte    | Organisateur de l'événement           | schema.org | Stéréolux                    |
| Sous-Événement   | UID      | UID d'un sous-événement               | schema.org | SL-2015-XYZ-009              |
| Super-Événement  | UID      | UID d'un sur-événement                | schema.org | SL-2015-XYZ-001              |
|                  |          |                                       |            |                              |
| **URLs**         |          |                                       |            |                              |
|                  |          |                                       |            |                              |
| URL              | URL      | URL vers le site ODEV2                | iCalendar  | http://ODEV2/event/XYZ123    |
| URL orga         | URL      | Url de l'event sur le site de l'orga  | schema.org | http://stereolux/event/XYZ123|
| URLs médias      | URLs     | Url de média compatible oEmbed        | schema.org | http://website/image.jpg     |
|                  |          |                                       |            |                              |
| **International**|          |                                       |            |                              |
|                  |          |                                       |            |                              |
| Langue           | Texte    | Langue de l'événement                 | ODE_V1     | fr (Français)                |
|                  |          |                                       |            |                              |
| **Tarifs**       |          |                                       |            |                              |
|                  |          |                                       |            |                              |
| Prix standard    | Nombre   | Prix au tarif normal                  | ODE_V2     | 10 (10 €)                    |
| Prix réduit      | Nombre   | Prix au tarif réduit                  | ODE_V2     | 7.5 (7.5 €)                  |
| Prix enfant      | Nombre   | Prix au tarif enfant                  | ODE_V2     | 5 (5 €)                      |
|                  |          |                                       |            |                              |
| **Contacts** [\[1\]](#liste-de-points-%C3%A0-d%C3%A9battre)||       |            |                              |
|                  |          |                                       |            |                              |
| Contact - Nom    | Texte    | Nom du contact                        | ODE_V1     | John Smith                   |
| Contact - Email  | Email    | Email du contact                      | ODE_V1     | john.smith@email.com         |
|                  |          |                                       |            |                              |
|**Catégorisation**|          |                                       |            |                              |
|                  |          |                                       |            |                              |
| Catégorie        | Texte    | Catégorie de l'événement              | ODE_V1     | Concert                      |
| Tags             | Texte    | Tags de l'événement                   | ODE_V1     | Rock;Alternatif;[...]        |


#### Liste de points à débattre:
* \[1\]: Ajout contact presse, ajout contact ticket ?


#### Sources:
* [RFC iCalendar](https://tools.ietf.org/html/rfc5545)
* [schema.org](http://schema.org/Event)
* [ODE V1](https://github.com/LiberTIC/ODE)




Définition technique des noms
-----------------------------

| Propriété         | iCalendar               | Json, CSV & XML        |
|:------------------|:------------------------|:-----------------------|
| Nom               | SUMMARY                 | name                   |
| UID               | UID                     | id                     |
| Description       | DESCRIPTION             | description            |
| Date début        | DTSTART                 | date_start             |
| Date fin          | DTEND                   | date_end               |
| Date création     | CREATED                 | date_created           |
| Date modification | LAST-MODIFIED           | date_modified          |
| Lieu              | LOCATION                | location_name          |
| Emplacement       | X-ODE-LOCATION-PRECISION| location_precision     |
| Géolocalisation   | GEO                     | geo                    |
| Capacité du lieu  | X-ODE-LOCATION-CAPACITY | location_capacity      |
| Participants      | X-ODE-ATTENDEES         | attendees              |
| Durée             | X-ODE-DURATION          | duration               |
| Status            | STATUS                  | status                 |
| Organisateur      | X-ODE-ORGANIZER         | organizer              |
| Sous-Événement    | X-ODE-SUBEVENT          | subevent               |
| Super-Événement   | X-ODE-SUPEREVENT        | superevent             |
| URL               | URL                     | url                    |
| URL orga          | X-ODE-URL-ORGA          | url_orga               |
| URLs medias       | X-ODE-URLS-MEDIAS       | urls_medias            |
| Langue            | X-ODE-LANGUAGE          | language               |
| Prix standard     | X-ODE-PRICE-STANDARD    | price_standard         |
| Prix réduit       | X-ODE-PRICE-REDUCED     | price_reduced          |
| Prix enfant       | X-ODE-PRICE-CHILDREN    | price_children         |
| Contact - Nom     | X-ODE-CONTACT-NAME      | contact_name           |
| Contact - Email   | X-ODE-CONTACT-EMAIL     | contact_email          |
| Catégorie         | X-ODE-CATEGORY          | category               |
| Tags              | X-ODE-TAGS              | tags                   |

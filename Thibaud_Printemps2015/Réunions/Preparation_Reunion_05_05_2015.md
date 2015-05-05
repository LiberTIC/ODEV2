####Ce que j'ai fait en 3 semaines

* Mise en place du poste de travail
* Apprentissage de Symfony2, Behat, PHPUnit, etc...
* Mise en place et analyse du travail effectué par les précédents stagiaires
* Analyse du cahier des charges et des RFC
* Préparation de la réunion


####A débattre:

1. CalDAV:
 	Il ne faut pas inciter à écrire des événements depuis client CalDAV, car il manquera des informations et il faudra de toute façon ajouter des informations depuis le site.

2. Réutilisateur:
	Selon CDC page 6, le réutilisateur doit s’inscrire sur le site avant de pouvoir récupérer des informations. Toujours d’actu?

3. Fournisseur:
	CDC page 6 parle d’événements « en attente » ou « publié ». Comment ça marche? Par défaut brouillon puis validation à la main? Ou par défaut validé, sauf quand précisé dans le formulaire du site.

4. CMS:
	CDC page 6 indique que le CMS doit permettre la traduction du contenu en plusieurs langues. Toujours d’actu ?

5. Modèle Événement:
	Il serait utile d’avoir des objets dans l’objet Événement (Ex: objet contact, tarif, etc.) Mais ce sera mal géré avec CSV

6. Multilingue:
	Toujours d’actu ?

7. Formats:
	Quels formats d’export / import ? CSV, XML, JSON, iCalendar ???



####Travail à effectuer:

* CMS:
  * Inscription / Connexion
  * Interface administrateur
  * Tableau de bord Fournisseur

* SITE:
  * Affichage sur le site des événements (https://github.com/adesigns/calendar-bundle)
  * Ajout Event Markup for Rich snippets Google (https://developers.google.com/structured-data/rich-snippets/events)

* IMPORT:
  * Utiliser un calendrier compatible CalDAV (?)
  * Ajouter un fichier (CSV,XML,JSON) sur le site
  * Ecrire dans un formulaire sur le site

* EXPORT:
  * API REST (CSV,XML,JSON, [h-event](http://microformats.org/wiki/h-event))
  * Abonnement depuis un calendrier compatible iCalendar

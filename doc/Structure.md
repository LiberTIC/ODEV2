# Explication de la structure technique d'ODEv2

## Symfony

Ce projet utilise Symfony 2.6

## CalDAV

ODEv2 se base sur l'extension CalDAV du protocol HTTP. Pour cela, nous utilisons Sabre DAV.

[Sabre DAV](https://github.com/fruux/sabre-dav) est un framework WebDAV et CalDAV pour PHP. Il permet en autre de se connecter à un calendrier depuis un client Calendrier tel que Apple iCal.

## Backend

Pour le backend, nous avons décidé d'utiliser PostgreSQL avec le [Pomm Project](https://github.com/pomm-project) et son [Bundle pour Symfony2](https://github.com/pomm-project/pomm-bundle).

Nous avons mis à disposition un [fichier SQL](../ODE.sql) pour préparer une base de données au projet. Cependant, libre à vous de l'adapter.

Pour utiliser Pomm, nous utilisons un Service Symfony: [PommManager](../src/AppBundle/Service/PommManager.php), que nous avons créé pour l'occasion. Ce service vous permettra d'effectuer des requêtes en toute simplicité.

Pour adapter SabreDAV à PostgreSQL, nous avons implémenté plusieurs interfaces:

* [Auth](../src/AppBundle/Backend/CalDAV/Auth.php): pour récuperer le hash d'un utilisateur
* [Principals](../src/AppBundle/Backend/CalDAV/Principals.php): pour gérer les "principals" (notion propre à CalDAV)
* [Calendar](../src/AppBundle/Backend/CalDAV/Calendar.php): pour gérer les calendriers, les évènements et les abonnements.

Cette dernière classe est la plus importante du projet. En effet, elle décrit la majorité des fonctions importantes tels que créer, modifier, supprimer un calendrier, un évènement, etc. Cette classe, bien qu'utilisée par SabreDAV, est aussi utilisée par le reste de l'application, notamment dans les controlleurs.

`Note: A dater du 16 Juin 2015, certaines des fonctionnalités de ces implémentations ne sont pas fini. Ce qui n'empèche pas d'avoir un calendrier fonctionnel, mais ne permet pas quelques fonctions tel que la délégation.`

En ce qui concerne la gestion des utilisateurs sur le site, nous utilisons [FOSUserBundle](https://github.com/FriendsOfSymfony/FOSUserBundle) avec un UserManager [à notre sauce](../src/AppBundle/Backend/Users/UserManager.php) qui récupère les données depuis la base postgreSQL.

## Service

Comme indiqué plus haut, nous utilisons un service fait-maison pour faire le lien avec Pomm. Il s'agit en fait d'un d'un ensemble de fonction simple réutilisé souvent dans le code (Ex: findAll, findById, InsertOne, updateOne, etc...). Ce service est situé [ici](../src/AppBundle/Service/PommManager.php).

## Entity

Afin d'obtenir une classe unique pour gérer les utilisateurs du site et ceux du serveur CalDAV, nous avons redéfini la classe BaseUser de FOSUSerBundle en ajoutant quelques fonctions. Cette classe est disponible ici: [User](../src/AppBundle/Entity/User.php).

Pour profiter d'un formulaire adapté à nos besoins, nous avons créé la classe [Event](../src/AppBundle/Entity/Event.php) qui permet de faire un lien entre le nom iCal et le nom Json d'une propriété (tel que défini dans le [Modèle d'un événement](../doc/Thibaud_Printemps2015/Modele_Evenement.md)).

## Form

Pour construire un formulaire (plutôt classe), nous utilisons [MopaBootstrapBundle](https://github.com/phiamo/MopaBootstrapBundle) qui permet d'ajouter de nombreuses options dans la classe de Form Type: [EventType](../src/AppBundle/Form/Type/EventType.php)

## Resources

Pour le routing, nous utilisons deux fichiers: [APIrouting](../src/AppBundle/Resources/config/APIrouting.yml) pour les routes lié à l'API et [routing](../src/AppBundle/Resources/config/routing.yml) pour le reste.

Dans le dossier [public](../src/AppBundle/Resources/public/), il est possible de trouver les librairies js et css que nous utilisons.

## Controller

L'application est divisé en 4 controlleurs:

**[DefaultController](../src/AppBundle/Controller/DefaultController.php)** est le controlleur le plus léger (2 fonctions) : Index du site et page de test

**[CalDAVController](../src/AppBundle/Controller/CalDAVController.php)** est le controlleur permettant de faire le lien avec SabreDAV.

**[BrowserController](../src/AppBundle/Controller/BrowserController.php)** est le controlleur définissant le Front-End de l'application.

**[APIController](../src/AppBundle/Controller/APIController)** est le controlleur délivrant l'API.


## Autre

Si vous avez des questions ou souhaitez plus d'informations sur certains points, n'hésitez pas à [créer une issue](https://github.com/LiberTIC/ODEV2/issues)

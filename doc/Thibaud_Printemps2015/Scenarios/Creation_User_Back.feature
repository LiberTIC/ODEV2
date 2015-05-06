#language: fr
Fonctionnalité: Accéder au Back End
		En tant qu'administrateur
		Pour créer, liste, supprimer, modifier des comptes utilisateurs

	Scénario: Accéder à l'interface d'administration par url
		Étant donné que je suis à "/"
		Quand je vais sur "/admin"
		Alors je devrais voir "Se connecter en tant qu'Administrateur"
		Et je devrais voir "Pseudo"
		Et je devrais voir "Mot de passe"
		Et le code de status de la réponse ne devrait pas être 500

	Scénario: Se connecter avec de bons identifiants
		Étant donné que je suis à "/"
		Quand je vais sur "/admin"
		Et que je saisis des identifiants de connexion
			| login    | password       |
			| admin    | securePassword |
		Et que je valide le formulaire
		Alors je devrais voir "Bonjour, admin"
		Et je ne devrais pas voir "Se connecter"
		Et je ne devrais pas voir "S'enregistrer"
		Et je ne devrais pas voir "Mauvais identifiants"
		Et je devrais voir "Se déconnecter"
		Et le code de status de la réponse ne devrait pas être 500

	Scénario: Se connecter avec de mauvais identifiants
		Étant donné que je suis à "/"
		Quand je vais sur "/admin"
		Et que je saisis des identifiants de connexion
			| login    | password    |
			| notAdmin | notPassword |
		Et que je valide le formulaire
		Alors je ne devrais pas voir "Bonjour, notAdmin"
		Et je devrais voir "Se connecter en tant qu'Administrateur"
		Et je devrais voir "Mauvais identifiants ou compte Non-Administrateur"
		Et je devrais voir "Pseudo"
		Et je devrais voir "Mot de passe"
		Et le code de status de la réponse ne devrait pas être 500

	Scénario: Lister les utilisateurs
		Étant donné que je suis à "/"
		Quand je vais sur "/admin"
		Et que je saisis des identifiants de connexion
			| login    | password       |
			| admin    | securePassword |
		Et que je valide le formulaire
		Et que je vais sur "/admin/list-users"
		Alors je devrais voir "Liste des utilisateurs"
		Et je devrais voir "unUtilisateurTest"
		Et le code de status de la réponse ne devrait pas être 500

	Scénario: Accéder à l'interface d'ajout d'utilisateurs par clic
		Étant donné que je suis à "/"
		Quand je vais sur "/admin"
		Et que je saisis des identifiants de connexion
			| login    | password       |
			| admin    | securePassword |
		Et que je valide le formulaire
		Et que je vais sur "/admin/list-users"
		Et que je clique sur "Ajouter un utilisateur"
		Alors je devrais voir "Ajouter un utilisateur"
		Et je devrais voir "Pseudo"
		Et je devrais voir "Mot de passe"
		Et je devrais voir "E-mail"
		Et je devrais voir "Enregistrer"
		Et le code de status de la réponse ne devrait pas être 500

	Scénario: Accéder à l'interface d'ajout d'utilisateurs par url
		Étant donné que je suis à "/"
		Quand je vais sur "/admin"
		Et que je saisis des identifiants de connexion
			| login    | password       |
			| admin    | securePassword |
		Et que je valide le formulaire
		Et que je vais sur "/admin/add-user"
		Alors je devrais voir "Ajouter un utilisateur"
		Et je devrais voir "Pseudo"
		Et je devrais voir "Mot de passe"
		Et je devrais voir "E-mail"
		Et je devrais voir "Enregistrer"
		Et le code de status de la réponse ne devrait pas être 500

	Scénario: Ajouter un utilisateur avec de bons identifiants
		Étant donné que je suis à "/"
		Quand je vais sur "/admin"
		Et que je saisis des identifiants de connexion
			| login    | password       |
			| admin    | securePassword |
		Et que je valide le formulaire
		Et que je vais sur "/admin/add-user"
		Et que je saisis des identifiants de création
			| login         | password      | password2     | email         |
			| compteTesting | motDePasse123 | motDepasse123 | test@test.com |
		Et que je valide le formulaire
		Alors je devrais voir "Liste des utilisateurs"
		Et je devrais voir "L'utilisateur a bien été créé"
		Et je devrais voir "compteTesting"
		Et le code de status de la réponse ne devrait pas être 500

	Scénario: Ajouter un utilisateur avec un pseudo déjà utilisé
		Étant donné que je suis à "/"
		Quand je vais sur "/admin"
		Et que je saisis des identifiants de connexion
			| login    | password       |
			| admin    | securePassword |
		Et que je valide le formulaire
		Et que je vais sur "/admin/add-user"
		Et que je saisis des identifiants de création
			| login         | password      | password2     | email         |
			| compteTesting | motDePasse123 | motDepasse123 | test@test.com |
		Et que je valide le formulaire
		Alors je devrais voir "Pseudo déjà utilisé"
		Et je devrais voir "Ajouter un utilisateur"
		Et je devrais voir "Pseudo"
		Et je devrais voir "Mot de passe"
		Et je devrais voir "E-mail"
		Et je devrais voir "Enregistrer"
		Et le code de status de la réponse ne devrait pas être 500

	Scénario: Ajouter un utilisateur avec un email déjà utilisé
		Étant donné que je suis à "/"
		Quand je vais sur "/admin"
		Et que je saisis des identifiants de connexion
			| login    | password       |
			| admin    | securePassword |
		Et que je valide le formulaire
		Et que je vais sur "/admin/add-user"
		Et que je saisis des identifiants de création
			| login          | password      | password2     | email         |
			| compteTesting2 | motDePasse123 | motDepasse123 | test@test.com |
		Et que je valide le formulaire
		Alors je devrais voir "Pseudo déjà utilisé"
		Et je ne devrais pas voir "L'utilisateur a bien été créé"
		Et je devrais voir "Ajouter un utilisateur"
		Et je devrais voir "Pseudo"
		Et je devrais voir "Mot de passe"
		Et je devrais voir "E-mail"
		Et je devrais voir "Enregistrer"
		Et le code de status de la réponse ne devrait pas être 500

	Scénario: Accéder à l'interface de modification d'un utilisateur
		Étant donné que je suis à "/"
		Quand je vais sur "/admin"
		Et que je saisis des identifiants de connexion
			| login   | password       |
			| admin   | securePassword |
		Et que je valide le formulaire
		Et que je vais sur "/admin/list-users"
		Et que je clique sur "Modifier"
		Alors je devrais voir "Modifier un utilisateur"
		Et le code de status de la réponse ne devrait pas être 500

	Scénario: Modifier un utilisateur
		Étant donné que je suis à "/"
		Quand je vais sur "/admin"
		Et que je saisis des identifiants de connexion
			| login   | password       |
			| admin   | securePassword |
		Et que je valide le formulaire
		Et que je vais sur "/admin/list-users"
		Et que je clique sur "Modifier"
		Et que je saisis des identifiants de création
			| login          | password      | password2     | email          |
			| compteTesting2 | motDePasse123 | motDepasse123 | test4@test.com |
		Et que je valide le formulaire
		Alors je devrais voir "L'utilisateur a bien été modifié"
		Et le code de status de la réponse ne devrait pas être 500

	Scénario: Accéder à l'interface de suppression d'un utilisateur
		Étant donné que je suis à "/"
		Quand je vais sur "/admin"
		Et que je saisis des identifiants de connexion
			| login | password       |
			| admin | securePassword |
		Et que je valide le formulaire
		Et que je vais sur "/admin/list-users"
		Et que je clique sur "Supprimer"
		Alors je devrais voir "Êtes-vous sur de vouloir supprimer cet utilisateur ?"
		Et je devrais voir "Oui"
		Et je devrais voir "Annuler"
	
	Scénario: Supprimer un utilisateur
		Étant donné que je suis sur "/"
		Quand je vais sur "/admin"
		Et que je saisis des identifiants de connexion
			| login | password       |
			| admin | securePassword |
		Et que je valide le formulaire
		Et que je vais sur "/admin/list-users"
		Et que je clique sur "Supprimer"
		Et que je clique sur "Oui"
		Alors je devrais voir "L'utilisateur a bien été supprimé"
		Et je devrais voir "Liste des utilisateurs"
		Et le code de status de la réponse ne devrait pas être 500
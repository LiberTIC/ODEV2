# language: fr
Fonctionnalité: Accéder au Front End
		Afin de se connecter
		En tant qu'utilisateur
		Si je n'arrive pas à me connecter, je devrais pouvoir avoir des choix alternatifs
		Tels que, réessayer, se créer un compte, réinitialiser son mot de passe

	Scénario: Accéder à l'interface de connexion par clic
		Étant donné que je suis à "/"
		Quand je clique sur le lien "Se connecter"
		Alors je devrais être sur "/login"
		Et je devrais voir "Se connecter"
		Et je devrais voir "Nom d'utilisateur"
		Et je devrais voir "Mot de passe"
		Et le code de status de la réponse ne devrait pas être 500

	Scénario: Accéder à l'interface de connexion par url
		Étant donné que je suis à "/"
		Quand je vais sur "/login"
		Alors je devrais voir "Se connecter"
		Et je devrais voir "Se connecter"
		Et je devrais voir "Nom d'utilisateur"
		Et je devrais voir "Mot de passe"
		Et le code de status de la réponse ne devrait pas être 500

	Scénario: Accéder à l'interface de création de compte par clic
		Étant donné que je suis à "/"
		Quand je clique sur le lien "Inscription"
		Alors je devrais voir "Inscription"
		Et je devrais voir "Nom d'utilisateur"
		Et je devrais voir "Mot de passe"
		Et je devrais voir "Vérification"
		Et je devrais voir "Adresse e-mail"
		Et le code de status de la réponse ne devrait pas être 500

	Scénario: Accéder à l'interface de création de compte par url
		Etant donné que je suis à "/"
		Quand je vais sur "/register"
		Alors je devrais voir "Inscription"
		Et je devrais voir "Nom d'utilisateur"
		Et je devrais voir "Mot de passe"
		Et je devrais voir "Vérification"
		Et je devrais voir "Adresse e-mail"
		Et le code de status de la réponse ne devrait pas être 500

	Scénario: Accéder à l'interface de réinitialisation de mot de passe par clic
		Étant donné que je suis à "/"
		Quand je vais sur "/login"
		Et que je clique sur le lien "Mot de passe oublié"
		Alors je devrais voir "Réinitialiser un mot de passe"
		Et je devrais voir "Adresse e-mail"
		Et je ne devrais pas voir "Nom d'utilisateur"
		Et le code de status de la réponse ne devrait pas être 500

	Scénario: Accéder à l'interface de réinitialisation de mot de passe par url
		Étant donné que je suis à "/"
		Quand je vais sur "/lostpassword"
		Alors je devrais voir "Adresse e-mail"
		Et je devrais voir "Réinitialiser un mot de passe"
		Et je ne devrais pas voir "Nom d'utilisateur"
		Et le code de status de la réponse ne devrait pas être 500

	Scénario: Se connecter avec de bons identifiants
		Étant donné que je suis à "/"
		Quand je vais sur "/login"
		Et que je saisis des identifiants de connexion
			| login      | password      |
			| compteTest | motDePasse123 |
		Et que je valide le formulaire
		Alors je devrais voir "Connecté"
		Et je ne devrais pas voir "Se connecter"
		Et je ne devrais pas voir "S'enregistrer"
		Et je ne devrais pas voir "Mauvais identifiants"
		Et je devrais voir "Déconnexion"
		Et le code de status de la réponse ne devrait pas être 500

	Scénario: Se connecter avec de mauvais identifiants
		Étant donné que je suis à "/"
		Quand je vais sur "/login"
		Et que je saisis des identifiants de connexion
			| login         | password          |
			| mauvaisCompte | mauvaisMotDePasse |
		Et que je valide le formulaire
		Alors je ne devrais pas voir "Bonjour, mauvaisCompte"
		Et je devrais voir "incorrect"
		Et je devrais voir "Oubli de mot de passe ?"
		Et je devrais voir "Nom d'utilisateur"
		Et je devrais voir "Mot de passe"
		Et le code de status de la réponse ne devrait pas être 500

	Scénario: S'enregistrer avec de bons identifiants
		Étant donné que je suis à "/"
		Quand je vais sur "/register"
		Et que je saisis des identifiants de création
			| login         | password      | password2     | email         |
			| compteTestTmp | motDePasse123 | motDePasse123 | testTmp@test.com |
		Et que je valide le formulaire
		Alors je devrais voir "Un mail a été envoyé à l'adresse"
		Et je devrais voir "Retourner à la page d'accueil"
		Et le code de status de la réponse ne devrait pas être 500
# Ne pas oublier de supprimer le compte!

	Scénario: S'enregistrer avec un pseudo déjà pris
		Étant donné que je suis à "/"
		Quand je vais sur "/register"
		Et que je saisis des identifiants de création
			| login         | password      | password2     | email         |
			| compteTest    | motDePasse123 | motDepasse123 | test@test.com |
		Et que je valide le formulaire
		Alors je devrais voir "Le nom d'utilisateur est déjà utilisé"
		Et je ne devrais pas voir "Un mail a été envoyé à l'adresse"
		Et je devrais voir "Inscription"
		Et le code de status de la réponse ne devrait pas être 500

	Scénario: S'enregistrer avec des mots de passe différents
		Étant donné que je suis à "/"
		Quand je vais sur "/register"
		Et que je saisis des identifiants de création
			| login          | password      | password2     | email         |
			| compteTestTmp2 | motDePasse123 | motDepasse789 | test@test.com |
		Et que je valide le formulaire
		Alors je devrais voir "Les deux mots de passe ne sont pas identiques"
		Et je ne devrais pas voir "Un mail a été envoyé à l'adresse"
		Et je devrais voir "Inscription"
		Et le code de status de la réponse ne devrait pas être 500

	Scénario: S'enregistrer avec une adresse email déjà prise
		Étant donné que je suis à "/"
		Quand je vais sur "/register"
		Et que je saisis des identifiants de création
			| login          | password      | password2     | email          |
			| compteTestTmp2 | motDePasse123 | motDepasse123 | test@test.com |
		Et que je valide le formulaire
		Alors je devrais voir "L'adresse e-mail est déjà utilisée"
		Et je ne devrais pas voir "Un mail a été envoyé à l'adresse"
		Et je devrais voir "Inscription"
		Et le code de status de la réponse ne devrait pas être 500

	Scénario: Réinitialiser un mot de passe
		Étant donné que je suis à "/"
		Quand je vais sur "/lostpassword"
		Et que je saisis une addresse email
			| email         |
			| test@test.com |
		Et que je valide le formulaire
		Alors je devrais voir "Un nouveau mot de passe a été envoyé à votre adresse mail"
		Et je devrais voir "Se connecter"
		Et le code de status de la réponse ne devrait pas être 500

	Scénario: Accéder à l'interface de connexion alors que l'utilisateur est déjà connecté
		Étant donné que je suis à "/"
		Quand je vais sur "/login"
		Et que je saisis des identifiants de connexion
			| login      | password      |
			| compteTest | motDePasse123 |
		Et que je valide le formulaire
		Et que je vais sur "/login"
		Alors je devrais voir "Connecté"
		Et je ne devrais pas voir "Se connecter"
		Et je ne devrais pas voir "S'enregistrer"
		Et le code de status de la réponse ne devrait pas être 500

	Scénario: Accéder à l'interface de création alors que l'utilisateur est déjà connecté
		Étant donné que je suis à "/"
		Quand je vais sur "/login"
		Et que je saisis des identifiants de connexion
			| login      | password      |
			| compteTest | motDePasse123 |
		Et que je valide le formulaire
		Et que je vais sur "/register"
		Alors je devrais voir "Connecté"
		Et je ne devrais pas voir "S'enregistrer"
		Et je ne devrais pas voir "Se connecter"
		Et le code de status de la réponse ne devrait pas être 500

	Scénario: Accéder à l'interface de réinitialisation alors que l'utilisateur est déjà connecté
		Étant donné que je suis à "/"
		Quand je vais sur "/login"
		Et que je saisis des identifiants de connexion
			| login      | password      |
			| compteTest | motDePasse123 |
		Et que je valide le formulaire
		Et que je vais sur "/register"
		Alors je devrais voir "Connecté"
		Et je ne devrais pas voir "S'enregistrer"
		Et je ne devrais pas voir "Réinitialiser un mot de passe"
		Et je ne devrais pas voir "Se connecter"
		Et le code de status de la réponse ne devrait pas être 500
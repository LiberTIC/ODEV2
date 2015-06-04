<?php

namespace AppBundle\Entity;

class Event
{

    // Modele disponible ici: https://github.com/LiberTIC/ODEV2/blob/master/doc/Thibaud_Printemps2015/Modele_Evenement.md
    
    /* Nom et description */

    private $nom;

    private $uid;

    private $description;

    /* Date et heure */

    private $date_debut;

    private $date_fin;

    private $date_creation;

    private $date_modification;

    /* Localisation */

    private $lieu;

    private $emplacement;

    private $geolocalisation;

    private $capacite_lieu;

    /* Organisation */

    private $participants;

    private $duree;

    private $status;

    private $organisateur;

    private $sous_evenement;

    private $super_evenement;

    /* URLs */

    private $URL;

    private $URL_orga;

    private $URLS_MEDIAS;

    /* International */

    private $langue;

    /* Tarifs */

    private $prix_standard;

    private $prix_reduit;

    private $prix_enfant;

    /* Contacts */

    private $contact_nom;

    private $contact_email;

    /* Categorisation */

    private $categorie;

    private $tags;

    
}
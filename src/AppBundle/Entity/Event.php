<?php

namespace AppBundle\Entity;

class Event
{

    // Modele disponible ici: https://github.com/LiberTIC/ODEV2/blob/master/doc/Thibaud_Printemps2015/Modele_Evenement.md
    

    private $properties = [

        /* Nom et description */
        "nom"               => null,
        "uid"               => null,
        "description"       => null,

        /* Date et heure */
        "date_debut"        => null,
        "date_fin"          => null,
        "date_creation"     => null,
        "date_modification" => null,

        /* Localisation */
        "lieu"              => null,
        "emplacement"       => null,
        "geolocalisation"   => null,
        "capacite_lieu"     => null,

        /* Organisation */
        "participants"      => null,
        "duree"             => null,
        "status"            => null,
        "organisateur"      => null,
        "sous_evenement"    => null,
        "super_evenement"   => null,

        /* URLs */
        "url"               => null,
        "url_orga"          => null,
        "urls_medias"       => null,

        /* International */
        "langue"            => null,

        /* Tarifs */
        "prix_standard"     => null,
        "prix_reduit"       => null,
        "prix_enfant"       => null,

        /* Contacts */
        "contact_nom"       => null,
        "contact_email"     => null,

        /* Categorisation */
        "categorie"         => null,
        "tags"              => null,
    ];

    public function __get($name) {
        return $this->properties[$name];
    }

    public function __set($name,$value) {
        $this->properties[$name] = $value;
    }

}
<?php

namespace AppBundle\Entity;

use Sabre\VObject;

class Event
{

    
    public $calendar = null;

    // Modele disponible ici: https://github.com/LiberTIC/ODEV2/blob/master/doc/Thibaud_Printemps2015/Modele_Evenement.md

    private $properties = [

        /* Nom et description */
        "nom"               => null,
        "uid"               => null,
        "description"       => null,

        /* Categorisation */
        "categorie"         => null,
        "tags"              => null,

        /* International */
        "langue"            => null,

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

        /* Contacts */
        "contact_nom"       => null,
        "contact_email"     => null,

        /* Tarifs */
        "prix_standard"     => null,
        "prix_reduit"       => null,
        "prix_enfant"       => null,
    ];

    private $convertTable = [
         /* Nom et description */
        "nom"               => "SUMMARY",
        "uid"               => "UID",
        "description"       => "DESCRIPTION",

        /* Categorisation */
        "categorie"         => "X-ODE-CATEGORY",
        "tags"              => "X-ODE-TAGS",

        /* International */
        "langue"            => "X-ODE-LANGUAGE",

        /* Date et heure */
        "date_debut"        => "DTSTART",
        "date_fin"          => "DTEND",
        "date_creation"     => "CREATED",
        "date_modification" => "LAST-MODIFIED",

        /* Localisation */
        "lieu"              => "LOCATION",
        "emplacement"       => "X-ODE-LOCATION-PRECISION",
        "geolocalisation"   => "GEO",
        "capacite_lieu"     => "X-ODE-LOCATION-CAPACITY",

        /* Organisation */
        "participants"      => "X-ODE-ATTENDEES",
        "duree"             => "X-ODE-DURATION",
        "status"            => "STATUS",
        "organisateur"      => "X-ODE-PROMOTER",
        "sous_evenement"    => "X-ODE-SUBEVENT",
        "super_evenement"   => "X-ODE-SUPEREVENT",

        /* URLs */
        "url"               => "URL",
        "url_orga"          => "X-ODE-URL-PROMOTER",
        "urls_medias"       => "X-ODE-URLS-MEDIAS",

        /* Contacts */
        "contact_nom"       => "X-ODE-CONTACT-NAME",
        "contact_email"     => "X-ODE-CONTACT-EMAIL",

        /* Tarifs */
        "prix_standard"     => "X-ODE-PRICE-STANDARD",
        "prix_reduit"       => "X-ODE-PRICE-REDUCED",
        "prix_enfant"       => "X-ODE-PRICE-CHILDREN",
    ];

    public function __get($name) {
        return $this->properties[$name];
    }

    public function __set($name,$value) {
        $this->properties[$name] = $value;
    }

    public function getVObject() {

        $vobject = new VObject\Component\VCalendar();

        $vobject->add(new VObject\Component\VEvent($vobject,'VEVENT'));

        $uid = strtoupper(substr($vobject->VEVENT->UID->getValue(),14)); // To remove the "sabre-vobject-" at the beginning
        $vobject->VEVENT->__set('UID',$uid);

        foreach($this->properties as $key => $value) {

            if ($value != null) {
                $name = $this->convertTable[$key];
                $vobject->VEVENT->add($name);
                $vobject->VEVENT->__set($name,$value);
            }
           
        }

        return $vobject;
    }

}
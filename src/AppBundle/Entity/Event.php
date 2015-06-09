<?php

namespace AppBundle\Entity;

use Sabre\VObject;

class Event
{

    
    public $calendarid = null;

    public $calendaruri = null;

    public $calendarname = null;

    // Modele disponible ici: https://github.com/LiberTIC/ODEV2/blob/master/doc/Thibaud_Printemps2015/Modele_Evenement.md

    private $properties = [

        /* Nom et description */
        "name"              => null,
        "id"                => null,
        "description"       => null,

        /* Categorisation */
        "category"          => null,
        "tags"              => null,

        /* International */
        "language"          => null,

        /* Date et heure */
        "date_start"        => null,
        "date_end"          => null,
        "date_created"      => null,
        "date_modified"     => null,

        /* Localisation */
        "location_name"     => null,
        "location_precision"=> null,
        "geo"               => null,
        "location_capacity" => null,

        /* Organisation */
        "attendees"         => null,
        "duration"          => null,
        "status"            => null,
        "promoter"          => null,
        "subevent"          => null,
        "superevent"        => null,

        /* URLs */
        "url"               => null,
        "url_promoter"      => null,
        "urls_medias"       => null,

        /* Contacts */
        "contact_name"       => null,
        "contact_email"     => null,

        /* Tarifs */
        "price_standard"    => null,
        "price_reduced"     => null,
        "price_children"    => null,
    ];

    private $convertTable = [
         /* Nom et description */
        "name"              => "SUMMARY",
        "id"                => "UID",
        "description"       => "DESCRIPTION",

        /* Categorisation */
        "category"         => "X-ODE-CATEGORY",
        "tags"              => "X-ODE-TAGS",

        /* International */
        "language"          => "X-ODE-LANGUAGE",

        /* Date et heure */
        "date_start"        => "DTSTART",
        "date_end"          => "DTEND",
        "date_created"      => "CREATED",
        "date_modified"     => "LAST-MODIFIED",

        /* Localisation */
        "location_name"     => "LOCATION",
        "location_precision"=> "X-ODE-LOCATION-PRECISION",
        "geo"               => "GEO",
        "location_capacity" => "X-ODE-LOCATION-CAPACITY",

        /* Organisation */
        "attendees"         => "X-ODE-ATTENDEES",
        "duration"          => "X-ODE-DURATION",
        "status"            => "STATUS",
        "promoter"          => "X-ODE-PROMOTER",
        "subevent"          => "X-ODE-SUBEVENT",
        "superevent"        => "X-ODE-SUPEREVENT",

        /* URLs */
        "url"               => "URL",
        "url_promoter"      => "X-ODE-URL-PROMOTER",
        "urls_medias"       => "X-ODE-URLS-MEDIAS",

        /* Contacts */
        "contact_name"       => "X-ODE-CONTACT-NAME",
        "contact_email"     => "X-ODE-CONTACT-EMAIL",

        /* Tarifs */
        "price_standard"    => "X-ODE-PRICE-STANDARD",
        "price_reduced"     => "X-ODE-PRICE-REDUCED",
        "price_children"    => "X-ODE-PRICE-CHILDREN",
    ];

    public function __get($name) {
        if ($name == 'calendarid')
            return $this->calendarid;

        if ($name == 'calendarname')
            return $this->calendarname;

        return $this->properties[$name];
    }

    public function __set($name,$value) {
        if ($name == 'calendarid')
            $this->calendarid = $value;

        if ($name == 'calendarname')
            $this->calendarname = $value;

        if ($name == 'date_start' || $name == 'date_end') {
            if (strpos($value,"T") === false) {
                $value = $value."T000000Z";
            }
        }

        $this->properties[$name] = $value;
    }

    public function __isset($name) {
        if ($name == 'calendarid' || $name == 'calendarname')
            return true;
        return array_key_exists($name,$this->properties);
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
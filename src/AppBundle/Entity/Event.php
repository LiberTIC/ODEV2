<?php

namespace AppBundle\Entity;

use Sabre\VObject;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Component\VEvent;
use Sabre\VObject\Reader;

/**
 * Class Event
 *
 * @package AppBundle\Entity
 */
class Event
{
    /**
     * @var null
     */
    public $calendar = null;

    /**
     * @var string
     */
    public $calendarid = null;

    /**
     * @var string
     */
    public $calendarname = null;

    /**
     * @var string
     */
    public $slug = null;


    /**
     * @var array
     * @link https://github.com/LiberTIC/ODEV2/blob/master/doc/Thibaud_Printemps2015/Modele_Evenement.md Model definition
     */
    private $properties = [

        /* Nom et description */
        'name' => null,
        'id' => null,
        'description' => null,

        /* Categorisation */
        'category' => null,
        'tags' => null,

        /* International */
        'language' => null,

        /* Date et heure */
        'date_start' => null,
        'date_end' => null,
        'date_created' => null,
        'date_modified' => null,

        /* Localisation */
        'location_name' => null,
        'location_precision' => null,
        'geo' => null,
        'location_capacity' => null,

        /* Organisation */
        'attendees' => null,
        'duration' => null,
        'status' => null,
        'promoter' => null,
        'subevent' => null,
        'superevent' => null,

        /* URLs */
        'url' => null,
        'url_promoter' => null,
        'urls_medias' => [],

        /* Contacts */
        'contact_name' => null,
        'contact_email' => null,

        /* Tarifs */
        'price_standard' => null,
        'price_reduced' => null,
        'price_children' => null,
    ];

    /**
     * @var array
     */
    public static $convertTable = [
         /* Nom et description */
        'name' => 'SUMMARY',
        'id' => 'UID',
        'description' => 'DESCRIPTION',

        /* Categorisation */
        'category' => 'X-ODE-CATEGORY',
        'tags' => 'X-ODE-TAGS',

        /* International */
        'language' => 'X-ODE-LANGUAGE',

        /* Date et heure */
        'date_start' => 'DTSTART',
        'date_end' => 'DTEND',
        'date_created' => 'CREATED',
        'date_modified' => 'LAST-MODIFIED',

        /* Localisation */
        'location_name' => 'LOCATION',
        'location_precision' => 'X-ODE-LOCATION-PRECISION',
        'geo' => 'GEO',
        'location_capacity' => 'X-ODE-LOCATION-CAPACITY',

        /* Organisation */
        'attendees' => 'X-ODE-ATTENDEES',
        'duration' => 'X-ODE-DURATION',
        'status' => 'STATUS',
        'promoter' => 'X-ODE-PROMOTER',
        'subevent' => 'X-ODE-SUBEVENT',
        'superevent' => 'X-ODE-SUPEREVENT',

        /* URLs */
        'url' => 'URL',
        'url_promoter' => 'X-ODE-URL-PROMOTER',
        'urls_medias' => 'X-ODE-URLS-MEDIAS',

        /* Contacts */
        'contact_name' => 'X-ODE-CONTACT-NAME',
        'contact_email' => 'X-ODE-CONTACT-EMAIL',

        /* Tarifs */
        'price_standard' => 'X-ODE-PRICE-STANDARD',
        'price_reduced' => 'X-ODE-PRICE-REDUCED',
        'price_children' => 'X-ODE-PRICE-CHILDREN',
    ];

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        if ($name == 'calendar') {
            return $this->calendar;
        }

        return $this->properties[$name];
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function __set($name, $value)
    {
        if ($name == 'calendar') {
            $this->calendar = $value;
        }

        if ($name == 'date_start' || $name == 'date_end') {
            if (is_string($value)) {
                if (strpos($value, 'T') === false) {
                    $value = $value.'T000000Z';
                }
                $value = new \DateTime($value);
            }
        }

        $this->properties[$name] = $value;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        if ($name == 'calendar') {
            return true;
        }

        return array_key_exists($name, $this->properties);
    }

    /**
     * @return VCalendar
     */
    public function getVObject()
    {
        $vCal = new VCalendar();

        $vCal->add(new VEvent($vCal, 'VEVENT'));

        $uid = strtoupper(substr($vCal->VEVENT->UID->getValue(), 14)); // To remove the "sabre-vobject-" at the beginning
        $vCal->VEVENT->__set('UID', $uid);

        foreach ($this->properties as $key => $value) {
            if ($value != null) {
                $name = self::$convertTable[$key];

                if (!$vCal->VEVENT->__isset($name)) {
                    $vCal->VEVENT->add($name);
                }

                $vCal->VEVENT->__set($name, $value);
            }
        }

        return $vCal;
    }

    /**
     * @param mixed $calendarData
     */
    public function loadFromCalData($calendarData)
    {
        $vCal = Reader::read($calendarData);

        $vEvent = $vCal->VEVENT;

        foreach (self::$convertTable as $jsonName => $icalName) {
            if ($vEvent->$icalName != null) {
                $value = $vEvent->$icalName->getParts();
                if (count($value) == 1 && !is_array($this->properties[$jsonName])) {
                    $value = $value[0];
                }
                $this->__set($jsonName, $value);
            }
        }
    }

    /**
     * @param VCalendar $vCal
     *
     * @return array
     */
    public static function extractData($vCal)
    {
        $vEvent = $vCal->VEVENT;

        $lobject = [];

        foreach (self::$convertTable as $jsonName => $icalName) {
            if ($data = $vEvent->__get($icalName)) {
                $parts = $data->getParts();
                if (sizeof($parts) == 1) {
                    $parts = $parts[0];
                }
                $lobject[$jsonName] = $parts;
            }
        }

        return $lobject;
    }
}

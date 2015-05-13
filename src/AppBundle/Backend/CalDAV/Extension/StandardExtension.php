<?php

namespace AppBundle\Backend\CalDAV\Extension;

class StandardExtension implements ExtensionInterface
{
    public $fields = [
        'name'               => 'SUMMARY',
        'description'        => 'DESCRIPTION',
        'date_start'         => 'DTSTART',
        'date_end'           => 'DTEND',
        'date_created'       => 'CREATED',
        'date_modified'      => 'LAST-MODIFIED',
        'location_name'      => 'LOCATION',
        'geo'                => 'GEO',
        'status'             => 'STATUS',
    ];

    public function extractData($component) {

        $customs = [];

        foreach ($this->fields as $jsonName => $iCalName) {
            $property = $component->__get($iCalName);
            if ($property != null) {
                $customs[$jsonName] = $property->getValue();
            } else {
                $customs[$jsonName] = null;
            }
        }

        return $customs;

    }
}
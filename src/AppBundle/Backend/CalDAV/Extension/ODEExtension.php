<?php

namespace AppBundle\Backend\CalDAV\Extension;

class ODEExtension implements ExtensionInterface
{
    public $extraFields = [
        'location_precision' => 'X-ODE-LOCATION-PRECISION',
        'location_capacity'  => 'X-ODE-LOCATION-CAPACITY',
        'attendees'          => 'X-ODE-ATTENDEES',
        'duration'           => 'X-ODE-DURATION',
        'organizer'          => 'X-ODE-ORGANIZER',
        'subevent'           => 'X-ODE-SUBEVENT',
        'superevent'         => 'X-ODE-SUPEREVENT',
        'url_orga'           => 'X-ODE-URL-ORGA',
        'urls_medias'        => 'X-ODE-URLS-MEDIAS',
        'language'           => 'X-ODE-LANGUAGE',
        'price_standard'     => 'X-ODE-PRICE-STANDARD',
        'price_reduced'      => 'X-ODE-PRICE-REDUCED',
        'price_children'     => 'X-ODE-PRICE-CHILDREN',
        'contact_name'       => 'X-ODE-CONTACT-NAME',
        'contact_email'      => 'X-ODE-CONTACT-EMAIL',
        'category'           => 'X-ODE-CATEGORY',
        'tags'               => 'X-ODE-TAGS',
    ];

    public function extractData($component)
    {
        $customs = [];
        
        foreach ($this->extraFields as $jsonName => $iCalName) {
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

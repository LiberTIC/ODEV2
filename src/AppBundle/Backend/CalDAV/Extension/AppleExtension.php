<?php

namespace AppBundle\Backend\CalDAV\Extension;

class AppleExtension implements ExtensionInterface
{
    public $field = 'X-APPLE-STRUCTURED-LOCATION';

    public function extractData($component)
    {

        $property = $component->__get($this->field);
        $geo = null;
        if ($property != null) {
            $value = $property->getValue();
            $geo = substr($value, 4);
        }

        return ["geo" => $geo];
    }
}

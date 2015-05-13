<?php

namespace AppBundle\Backend\CalDAV\Extension;

interface ExtensionInterface
{
    public function extractData($component); // must return an array
}

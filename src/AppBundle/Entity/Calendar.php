<?php

namespace AppBundle\Entity;

class Calendar
{

    public $user = null;
    public $events = [];

    public $id = null;
    public $uri = null;
    public $displayName = null;
    public $principalUri = null;

    public function __construct($raw,$user)
    {
        $this->user = $user;

        $this->id = $raw['id'];
        $this->uri = $raw['uri'];
        $this->displayName = $raw['{DAV:}displayname'];
        $this->principalUri = $raw['principaluri'];
    }

    public function addEvent($event) {
        $this->events[] = $event;
    }
}
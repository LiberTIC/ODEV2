<?php

namespace AppBundle\Entity;

class Calendar
{

    public $user = null;
    public $events = [];

    public $id = null;
    public $uri = null;

    public function __construct($id,$uri,$user)
    {
        $this->id = $id;
        $this->uri = $uri;
        $this->user = $user;
    }

    public function addEvent($event) {
        $this->events[] = $event;
    }
}
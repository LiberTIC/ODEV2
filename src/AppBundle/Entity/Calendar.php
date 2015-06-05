<?php

namespace AppBundle\Entity;

class Calendar
{

    public $user = null;
    public $events = [];

    public $id = null;

    public function __construct($id,$user)
    {
        $this->id = $id;
        $this->user = $user;
    }

    public function addEvent($event) {
        $this->events[] = $event;
    }
}
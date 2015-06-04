<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class BrowserController extends Controller
{

    public function eventCreateAction() {

        




        return new Response("eventCreateAction");
    }

    public function eventReadAction($uid) {

        return new Response("eventReadAction / uid: ".$uid);
    }

    public function eventUpdateAction($uid) {

        return new Response("eventUpdateAction / uid: ".$uid);
    }

    public function eventDeleteAction($uid) {

        return new Response("eventDeleteAction / uid: ".$uid);
    }

}
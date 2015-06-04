<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Event;
use AppBundle\Form\Type\EventType;

class BrowserController extends Controller
{

    public function eventCreateAction(Request $request) {

        $event = new Event();
        $form = $this->createForm(new EventType(),$event,["csrf_protection" => false]);

        $form->handleRequest($request);

        if ($form->isValid()) {

            return new Response("Oh yiiiss / ".$event->nom);
        }

        return $this->render('browser/event.html.twig', array(
            'form' => $form->createView(),
        ));

        //return new Response("eventCreateAction");
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
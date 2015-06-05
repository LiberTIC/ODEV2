<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use AppBundle\Entity\Event;
use AppBundle\Entity\Calendar;
use AppBundle\Form\Type\EventType;
use AppBundle\Backend;

class BrowserController extends Controller
{

    /*          EVENT         */

    public function eventCreateAction(Request $request) {

        $event = new Event();
        $form = $this->createForm(new EventType(),$event,["csrf_protection" => false]);

        $form->handleRequest($request);

        if ($form->isValid()) {

            return new Response($event->getVObject()->serialize());
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

    /*          CALENDAR          */

    public function calendarHomeAction() {

        $calendarBackend = new Backend\CalDAV\Calendar($this->get('esmanager'),$this->get('converter'));

        $tkn = $this->get('security.context')->getToken();

        $rawCalendars = $calendarBackend->getCalendars();

        $calendars = [];
        $calendarsUser = [];


        foreach($rawCalendars as $raw) {
            $calendars[] = new Calendar($raw,null);
        }


        foreach($calendars as $calendar) {
            $calendar->events = $calendarBackend->getCalendarObjects($raw['id']);
            $calendar->user = substr($calendar->principalUri,11);
        }

        if ( !$tkn instanceof AnonymousToken ) {
            // list all calendars + user's calendar
            $usr = $tkn->getUser();
            $username = $usr->getUsernameCanonical();

            foreach($calendars as $key => $calendar) {
                if ($calendar->principalUri == 'principals/'.$username) {
                    $calendarsUser[] = $calendars[$key];
                    unset($calendars[$key]);
                }
            }
        }

        return $this->render('browser/calendar_home.html.twig', array(
            'calendars' => $calendars,
            'calendarsUser' => $calendarsUser,
        ));

    }

    public function calendarCreateAction() {

        return new Response("calendarCreateAction");
    }

    public function calendarReadAction($uid) {

        return new Response("calendarReadAction / uid: ".$uid);
    }

    public function calendarUpdateAction($uid) {

        return new Response("calendarUpdateAction / uid: ".$uid);
    }

    public function calendarDeleteAction($uid) {

        return new Response("calendarDeleteAction / uid: ".$uid);
    }

}
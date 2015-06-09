<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;

use PommProject\Foundation\Where;

use AppBundle\Entity\Event;
use AppBundle\Entity\Calendar;
use AppBundle\Form\Type\EventType;
use AppBundle\Form\Type\CalendarType;
use AppBundle\Backend;

class BrowserController extends Controller
{

    /*          EVENT         */

    public function eventHomeAction() {

        $tkn = $this->get('security.context')->getToken();

        $calendarBackend = new Backend\CalDAV\Calendar($this->get('pmanager'),$this->get('converter'));

        $rawEvents = $calendarBackend->getAllCalendarObjects();

        $events = [];
        $eventsUser = [];

        foreach($rawEvents as $raw) {
            $event = new Event();

            foreach($raw->extracted_data as $name => $value) {
                $event->__set($name,$value);
            }


            $cal = $calendarBackend->getCalendarById($raw->calendarid);

            $event->calendar = $cal;

            $events[] = $event;
        }

        if ( !$tkn instanceof AnonymousToken ) {

            $usr = $tkn->getUser();
            $username = $usr->getUsernameCanonical();

            $calendarsUser = $calendarBackend->getCalendarsForUser('principals/'.$username);

            $calIds = [];

            foreach($calendarsUser as $cal) {
                $calIds[] = $cal['id'];
            }


            foreach($events as $key => $event) {
                if (in_array($event->calendar->uid,$calIds)) {
                    $eventsUser[] = $event;
                    unset($events[$key]);
                }
            }
        }

        return $this->render('browser/event_home.html.twig', array(
            'events' => $events,
            'eventsUser' => $eventsUser,
        ));
    }

    public function eventCreateAction(Request $request) {

        $this->denyAccessUnlessGranted('ROLE_USER', null, 'Unable to access this page!');

        $usr = $this->get('security.context')->getToken()->getUser();
        $username = $usr->getUsernameCanonical();

        $calendarBackend = new Backend\CalDAV\Calendar($this->get('pmanager'),$this->get('converter'));

        $rawCalendars = $calendarBackend->getCalendarsForUser('principals/'.$username);

        $calendars = [];
        foreach($rawCalendars as $raw) {
            $calendars[$raw['id']] = $raw['{DAV:}displayname'];
        }

        $event = new Event();
        $form = $this->createForm(new EventType($calendars),$event,["csrf_protection" => false]);

        $form->handleRequest($request);

        if ($form->isValid()) {

            return new Response($event->getVObject()->serialize());
        }

        return $this->render('browser/event_create.html.twig', array(
            'form' => $form->createView(),
        ));

        //return new Response("eventCreateAction");
    }

    public function eventReadAction($uri) {

        return new Response("eventReadAction / uid: ".$uri);
    }

    public function eventUpdateAction($uri) {

        return new Response("eventUpdateAction / uid: ".$uri);
    }

    public function eventDeleteAction($uri) {

        return new Response("eventDeleteAction / uid: ".$uri);
    }

    /*          CALENDAR          */

    public function calendarHomeAction() {

        $calendarBackend = new Backend\CalDAV\Calendar($this->get('pmanager'),$this->get('converter'));

        $tkn = $this->get('security.context')->getToken();

        $rawCalendars = $calendarBackend->getAllCalendars();

        $calendars = [];
        $calendarsUser = [];


        foreach($rawCalendars as $raw) {
            $calendars[] = new Calendar($raw,null);
        }


        foreach($calendars as $calendar) {
            $calendar->events = $calendarBackend->getCalendarObjects($calendar->id);
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

    public function calendarCreateAction(Request $request) {

        $this->denyAccessUnlessGranted('ROLE_USER', null, 'Unable to access this page!');

        $usr = $this->get('security.context')->getToken()->getUser();

        $calendar = new Calendar(null,$usr);

        $form = $this->createForm(new CalendarType(),$calendar,["csrf_protection" => false]);

        $form->handleRequest($request);

        if ($form->isValid()) {

            $calendarUri = $this->toAscii($calendar->displayName);

            // Get the next calendarUri possible with the name (Example: truc, truc-1, truc-2, etc..)
            $i = -1;
            do {
                $i++;
                $where = Where::create("uri = $*",[$calendarUri.($i==0?'':'-'.$i)]);
                $calendars = $this->get('pmanager')->findWhere('public','calendar',$where);
            } while(sizeof($calendars->extract()) != 0);

            $calendarUri = $calendarUri.($i==0?'':'-'.$i);

            $calendarBackend = new Backend\CalDAV\Calendar($this->get('pmanager'),$this->get('converter'));

            $raw = [
                '{DAV:}displayname' => $calendar->displayName,
                '{urn:ietf:params:xml:ns:caldav}calendar-description' => $calendar->description
            ];

            $principalUri = 'principals/'.$usr->getUsernameCanonical();

            $calendarBackend->createCalendar($principalUri,$calendarUri,$raw);

            $this->addFlash('success','Le calendrier "'.$calendar->displayName.'" a bien été créé.');

            return $this->redirectToRoute('calendar_home');
        }

        return $this->render('browser/calendar_create.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function calendarReadAction($uri) {

        return new Response("calendarReadAction / uri: ".$uri);
    }

    public function calendarUpdateAction($uri) {

        return new Response("calendarUpdateAction / uri: ".$uri);
    }

    public function calendarDeleteAction($uri) {

        return new Response("calendarDeleteAction / uri: ".$uri);
    }




    // thanks to: http://cubiq.org/the-perfect-php-clean-url-generator

    protected function toAscii($str, $replace=array(), $delimiter='-') {
        if( !empty($replace) ) {
            $str = str_replace((array)$replace, ' ', $str);
        }

        $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
        $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
        $clean = strtolower(trim($clean, '-'));
        $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);

        return $clean;
    }

}
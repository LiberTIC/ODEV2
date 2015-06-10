<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;

use PommProject\Foundation\Where;

use AppBundle\Entity\Event;
use AppBundle\Form\Type\EventType;
use AppBundle\Form\Type\CalendarType;
use AppBundle\Backend;

use AppBundle\Model\Ode\PublicSchema as Model;

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

        $calendars = $calendarBackend->getAllCalendars();

        $calendarsOthers = [];
        $calendarsUser = [];


        foreach($calendars as $calendar) {
            $calendar->events = $calendarBackend->getCalendarObjects($calendar->uid);
            $calendar->user = substr($calendar->principalUri,11);
        }

        if ( !$tkn instanceof AnonymousToken ) {
            // list all calendars + user's calendar
            $usr = $tkn->getUser();
            $username = $usr->getUsernameCanonical();


            foreach($calendars as $key => $calendar) {
                if ($calendar->principalUri == 'principals/'.$username) {
                    $calendarsUser[] = $calendar;
                } else {
                    $calendarsOthers[] = $calendar;
                }
            }
        } else {
            $calendarsOthers = $calendars;
        }

        return $this->render('browser/calendar_home.html.twig', array(
            'calendars' => $calendarsOthers,
            'calendarsUser' => $calendarsUser,
        ));

    }

    public function calendarCreateAction(Request $request) {

        $this->denyAccessUnlessGranted('ROLE_USER', null, 'Unable to access this page!');

        $usr = $this->get('security.context')->getToken()->getUser();

        $form = $this->createForm(new CalendarType(),null,["csrf_protection" => false]);

        $form->handleRequest($request);

        if ($form->isValid()) {

            $values = $form->getData();

            $calendarUri = $this->generateCalendarUri($values['displayname']);

            $calendarBackend = new Backend\CalDAV\Calendar($this->get('pmanager'),$this->get('converter'));

            $raw = [
                '{DAV:}displayname' => $values['displayname'],
                '{urn:ietf:params:xml:ns:caldav}calendar-description' => $values['description']
            ];

            $principalUri = 'principals/'.$usr->getUsernameCanonical();

            $calendarBackend->createCalendar($principalUri,$calendarUri,$raw);

            $this->addFlash('success','Le calendrier "'.$values['displayname'].'" a bien été créé.');

            return $this->redirectToRoute('calendar_home');
        }

        return $this->render('browser/calendar_create.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function calendarReadAction($uri) {

        $where = Where::create("uri = $*",[$uri]);

        $calendar = $this->get('pmanager')->findWhere('public','calendar',$where)->get(0);

        if ($calendar == null) {
            return $this->redirectToRoute('calendar_home');
        }

        $ownCalendar = false;

        $tkn = $this->get('security.context')->getToken();
        if ( !$tkn instanceof AnonymousToken ) {
            $usr = $tkn->getUser();
            $username = $usr->getUsernameCanonical();

            if ($calendar->principaluri == "principals/".$username) {
                $ownCalendar = true;
            }
        } 

        return $this->render('browser/calendar_read.html.twig', array(
            'calendar' => $calendar,
            'ownCalendar' => $ownCalendar,
        ));
    }

    public function calendarUpdateAction(Request $request, $uri) {

        $this->denyAccessUnlessGranted('ROLE_USER', null, 'Unable to access this page!');

        $usr = $this->get('security.context')->getToken()->getUser();

        $where = Where::create("uri = $*",[$uri]);

        $calendar = $this->get('pmanager')->findWhere('public','calendar',$where)->get(0);

        if ($calendar == null) {
            return $this->redirectToRoute('calendar_home');
        }

        if ($calendar->principaluri != 'principals/'.$usr->getUsernameCanonical()) {
            $this->addFlash('danger','Ce calendrier ne vous appartient pas.');

            return $this->redirectToRoute('calendar_read',['uri'=>$uri]);
        }

        $previousName = $calendar->displayname;

        $form = $this->createForm(new CalendarType(),$calendar,["csrf_protection" => false]);

        $form->handleRequest($request);

        if ($form->isValid()) {

            if ($previousName != $calendar->displayname) {
                $calendar->uri = $this->generateCalendarUri($calendar->displayname);
            }
            
            $this->get('pmanager')->updateOne('public','calendar',$calendar,['uri','displayname','description']);

            $this->addFlash('success','Le calendrier "'.$calendar->displayName.'" a bien été modifié.');

            return $this->redirectToRoute('calendar_read',['uri'=>$calendar->uri]);
        }

        return $this->render('browser/calendar_update.html.twig', array(
            'form' => $form->createView(),
            'uri' => $uri
        ));
    }

    public function calendarDeleteAction($uri) {

        $where = Where::create("uri = $*",[$uri]);

        $calendar = $this->get('pmanager')->findWhere('public','calendar',$where)->get(0);

        if ($calendar == null) {
            return $this->redirectToRoute('calendar_home');
        }

        $calendarBackend = new Backend\CalDAV\Calendar($this->get('pmanager'),$this->get('converter'));

        $calendarBackend->deleteCalendar($calendar->uid);

        $this->addFlash('success','Le calendrier "'.$calendar->displayName.'" a bien été supprimé.');

        return $this->redirectToRoute('calendar_home');
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

    // Get the next calendarUri possible with the name (Example: truc, truc-1, truc-2, etc..)
    protected function generateCalendarUri($str) {
        $calendarUri = $this->toAscii($str);
           
        $i = -1;
        do {
            $i++;
            $where = Where::create("uri = $*",[$calendarUri.($i==0?'':'-'.$i)]);
            $calendars = $this->get('pmanager')->findWhere('public','calendar',$where);
        } while(sizeof($calendars->extract()) != 0);

        return $calendarUri.($i==0?'':'-'.$i);
    }

}
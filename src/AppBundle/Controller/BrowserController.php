<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;

use PommProject\Foundation\Where;

use Embed\Embed;

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

        $calendarBackend = new Backend\CalDAV\Calendar($this->get('pmanager'),$this->generateUrl('event_read'));

        $rawEvents = $calendarBackend->getAllCalendarObjects();

        $events = [];
        $eventsUser = [];

        $calendars = [];

        foreach($rawEvents as $raw) {
            $event = new Event();

            foreach($raw->extracted_data as $name => $value) {
                $event->__set($name,$value);
            }


            if (!isset($calendars[$raw->calendarid])) {
                $cal = $calendarBackend->getCalendarById($raw->calendarid);
                $event->calendar = $cal;
                $calendars[$raw->calendarid] = $cal;
            } else {
                $event->calendar = $calendars[$raw->calendarid];
            }

            $event->id = $raw->uid;            

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

        $eventsUser = $this->sortByDateField($eventsUser,"date_start");
        $events = $this->sortByDateField($events,"date_start");

        return $this->render('browser/event_home.html.twig', array(
            'events' => $events,
            'eventsUser' => $eventsUser,
        ));
    }

    public function eventCreateAction(Request $request) {

        $this->denyAccessUnlessGranted('ROLE_USER', null, 'Unable to access this page!');

        $usr = $this->get('security.context')->getToken()->getUser();
        $username = $usr->getUsernameCanonical();

        $calendarBackend = new Backend\CalDAV\Calendar($this->get('pmanager'),$this->generateUrl('event_read'));

        $rawCalendars = $calendarBackend->getCalendarsForUser('principals/'.$username);

        if ($rawCalendars == []) {
            $this->addFlash('danger','Vous devez créer au moins 1 calendrier avant de pouvoir créer des événements.');

            return $this->redirectToRoute('event_home');
        }

        $calendars = [];
        foreach($rawCalendars as $raw) {
            $calendars[$raw['id']] = $raw['{DAV:}displayname'];
        }

        $where = Where::create('uri = $*',[$request->query->get('calendar')]);
        $raws = $this->get('pmanager')->findWhere('public','calendar',$where);
        
        $calendar = null;
        if ($raws->count() > 0) {
            $calendar = $raws->get(0)->uid;
        }

        $event = new Event();
        $form = $this->createForm(new EventType($calendars,$calendar),$event,["csrf_protection" => false]);

        $form->handleRequest($request);

        if ($form->isValid()) {

            $vevent = $event->getVObject();

            $calendarBackend->createCalendarObject($event->calendarid,$vevent->VEVENT->UID.".ics",$vevent->serialize());
            
            $this->addFlash('success',"L'événement a bien été créé.");

            return $this->redirectToRoute('event_read',['uri' => $vevent->VEVENT->UID]);
        }

        return $this->render('browser/event_create.html.twig', array(
            'form' => $form->createView(),
        ));

        //return new Response("eventCreateAction");
    }

    public function eventReadAction($uri) {

        $rawEvent = $this->get('pmanager')->findById('public','calendarobject',$uri);

        $event = new Event();
        $event->loadFromCalData($rawEvent->calendarData);

        $calendar = $this->get('pmanager')->findById('public','calendar',$rawEvent->calendarid);

        $ownEvent = false;

        $tkn = $this->get('security.context')->getToken();
        if ( !$tkn instanceof AnonymousToken ) {
            $usr = $tkn->getUser();
            $username = $usr->getUsernameCanonical();

            if ($calendar->principaluri == "principals/".$username) {
                $ownEvent = true;
            }
        }

        return $this->render('browser/event_read.html.twig',array(
            'event' => $event,
            'calendar' => $calendar,
            'ownEvent' => $ownEvent
        ));
    }

    public function eventUpdateAction(Request $request, $uri) {

        $this->denyAccessUnlessGranted('ROLE_USER', null, 'Unable to access this page!');

        $usr = $this->get('security.context')->getToken()->getUser();
        $username = $usr->getUsernameCanonical();

        $rawEvent = $this->get('pmanager')->findById('public','calendarobject',$uri);

        if ($rawEvent == null) {
            return $this->redirectToRoute('event_home');
        }

        $event = new Event();
        $event->loadFromCalData($rawEvent->calendarData);

        $form = $this->createForm(new EventType(),$event,["csrf_protection" => false]);

        $form->handleRequest($request);

        if ($form->isValid()) {

            $vevent = $event->getVObject();

            $calendarBackend = new Backend\CalDAV\Calendar($this->get('pmanager'),$this->generateUrl('event_read'));

            $calendarBackend->updateCalendarObject($rawEvent->calendarid,$uri.".ics",$vevent->serialize());
            
            $this->addFlash('success',"L'événement a bien été modifié.");

            return $this->redirectToRoute('event_read',['uri' => $uri]);
        }

        return $this->render('browser/event_update.html.twig', array(
            'form' => $form->createView(),
            'uri' => $uri
        ));
    }

    public function eventDeleteAction($uri) {

        $this->denyAccessUnlessGranted('ROLE_USER', null, 'Unable to access this page!');

        $usr = $this->get('security.context')->getToken()->getUser();

        $where = Where::create("uid = $*",[$uri]);

        $events = $this->get('pmanager')->findWhere('public','calendarobject',$where);

        if ($events->count() == 0) {
            return $this->redirectToRoute('event_home');
        }

        $event = $events->get(0);

        $calendar = $this->get('pmanager')->findById('public','calendar',$event->calendarid);

        if ($calendar->principaluri != 'principals/'.$usr->getUsernameCanonical()) {
            $this->addFlash('danger','Cet événement ne fait pas parti de vos calendriers.');

            return $this->redirectToRoute('event_read',['uri'=>$uri]);
        }

        $calendarBackend = new Backend\CalDAV\Calendar($this->get('pmanager'),$this->generateUrl('event_read'));

        $calendarBackend->deleteCalendarObject($event->calendarid,$event->uri);

        $this->addFlash('success','L\'événement a bien été supprimé.');

        return $this->redirectToRoute('event_home');
    }

    /*          CALENDAR          */

    public function calendarHomeAction() {

        $calendarBackend = new Backend\CalDAV\Calendar($this->get('pmanager'),$this->generateUrl('event_read'));

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
            foreach($calendars as $calendar){
                $calendarsOthers[] = $calendar;
            }
        }

        $calendarsUser = $this->sortByStringField($calendarsUser,"displayname");
        $calendarsOthers = $this->sortByStringField($calendarsOthers,"displayname");

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

            $calendarBackend = new Backend\CalDAV\Calendar($this->get('pmanager'),$this->generateUrl('event_read'));

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

        $calendars = $this->get('pmanager')->findWhere('public','calendar',$where);

        if ($calendars->count() == 0) {
            return $this->redirectToRoute('calendar_home');
        }

        $calendar = $calendars->get(0);

        $ownCalendar = false;

        $tkn = $this->get('security.context')->getToken();
        if ( !$tkn instanceof AnonymousToken ) {
            $usr = $tkn->getUser();
            $username = $usr->getUsernameCanonical();

            if ($calendar->principaluri == "principals/".$username) {
                $ownCalendar = true;
            }
        }

        $where = Where::create("calendarid = $*",[$calendar->uid]);

        $rawEvents = $this->get('pmanager')->findWhere('public','calendarobject',$where);

        $events = [];

        foreach($rawEvents as $raw) {
            $event = new Event();

            foreach($raw->extracted_data as $name => $value) {
                $event->__set($name,$value);
            }

            $events[] = $event;
        }

        $exportUrl = $this->generateUrl('caldav',['url' => 'calendars/'.substr($calendar->principaluri,11).'/'.$calendar->uri,'export' => '']);

        return $this->render('browser/calendar_read.html.twig', array(
            'calendar' => $calendar,
            'ownCalendar' => $ownCalendar,
            'events' => $events,
            'exportUrl' => $exportUrl,
        ));
    }

    public function calendarUpdateAction(Request $request, $uri) {

        $this->denyAccessUnlessGranted('ROLE_USER', null, 'Unable to access this page!');

        $usr = $this->get('security.context')->getToken()->getUser();

        $where = Where::create("uri = $*",[$uri]);

        $calendars = $this->get('pmanager')->findWhere('public','calendar',$where);

        if ($calendars->count() == 0) {
            return $this->redirectToRoute('calendar_home');
        }

        $calendar = $calendars->get(0);

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

        $this->denyAccessUnlessGranted('ROLE_USER', null, 'Unable to access this page!');

        $usr = $this->get('security.context')->getToken()->getUser();

        $where = Where::create("uri = $*",[$uri]);

        $calendars = $this->get('pmanager')->findWhere('public','calendar',$where);

        if ($calendars->count() == 0) {
            return $this->redirectToRoute('calendar_home');
        }

        $calendar = $calendars->get(0);

        if ($calendar->principaluri != 'principals/'.$usr->getUsernameCanonical()) {
            $this->addFlash('danger','Ce calendrier ne vous appartient pas.');

            return $this->redirectToRoute('calendar_read',['uri'=>$uri]);
        }

        $calendarBackend = new Backend\CalDAV\Calendar($this->get('pmanager'),$this->generateUrl('event_read'));

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

    protected function sortByStringField($data,$fieldName) {
        usort($data,function ($a,$b) use ($fieldName) {
            return strcmp($a->$fieldName,$b->$fieldName);
        });
        return $data;
    }

    protected function sortByDateField($data,$fieldName) {
        usort($data,function ($a,$b) use ($fieldName) {
            return $a->$fieldName > $b->$fieldName;
        });
        return $data;
    }

}
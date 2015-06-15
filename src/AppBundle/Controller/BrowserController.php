<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use PommProject\Foundation\Where;
use AppBundle\Entity\Event;
use AppBundle\Form\Type\EventType;
use AppBundle\Form\Type\CalendarType;
use AppBundle\Backend\CalDAV\Calendar;

/**
 * Class BrowserController
 *
 * @package AppBundle\Controller
 */
class BrowserController extends Controller
{
    /**
     * @return Response
     */
    public function eventHomeAction()
    {
        $tkn = $this->get('security.token_storage')->getToken();

        $calendarBackend = new Calendar($this->get('pmanager'));

        $rawEvents = $calendarBackend->getAllCalendarObjects();

        $events = [];
        $eventsUser = [];

        $calendars = [];

        foreach ($rawEvents as $raw) {
            $event = new Event();

            foreach ($raw->extracted_data as $name => $value) {
                $event->__set($name, $value);
            }

            if (!isset($calendars[$raw->calendarid])) {
                $cal = $calendarBackend->getCalendarById($raw->calendarid);
                $event->calendar = $cal;
                $calendars[$raw->calendarid] = $cal;
            } else {
                $event->calendar = $calendars[$raw->calendarid];
            }

            $event->id = $raw->uid;

            $event->slug = $raw->slug;

            $events[] = $event;
        }

        if (!$tkn instanceof AnonymousToken) {
            $usr = $tkn->getUser();
            $username = $usr->getUsernameCanonical();

            $calendarsUser = $calendarBackend->getCalendarsForUser('principals/'.$username);

            $calIds = [];

            foreach ($calendarsUser as $cal) {
                $calIds[] = $cal['id'];
            }

            foreach ($events as $key => $event) {
                if (in_array($event->calendar->uid, $calIds)) {
                    $eventsUser[] = $event;
                    unset($events[$key]);
                }
            }
        }

        $eventsUser = $this->sortByDateField($eventsUser, 'date_start');
        $events = $this->sortByDateField($events, 'date_start');

        return $this->render('browser/event_home.html.twig', array(
            'events' => $events,
            'eventsUser' => $eventsUser,
        ));
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function eventCreateAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'Unable to access this page!');

        $usr = $this->get('security.token_storage')->getToken()->getUser();
        $username = $usr->getUsernameCanonical();

        $calendarBackend = new Calendar($this->get('pmanager'), $this->generateUrl('event_read', [], true), $this->get('slugify'));

        $rawCalendars = $calendarBackend->getCalendarsForUser('principals/'.$username);

        if ($rawCalendars == []) {
            $this->addFlash('danger', 'Vous devez créer au moins 1 calendrier avant de pouvoir créer des événements.');

            return $this->redirectToRoute('event_home');
        }

        $calendars = [];
        foreach ($rawCalendars as $raw) {
            $calendars[$raw['id']] = $raw['{DAV:}displayname'];
        }

        $where = Where::create('slug = $*', [$request->query->get('calendar')]);
        $raws = $this->get('pmanager')->findWhere('public', 'calendar', $where);

        $calendar = null;
        if ($raws->count() > 0) {
            $calendar = $raws->get(0)->uid;
        }

        $event = new Event();
        $form = $this->createForm(new EventType($calendars, $calendar), $event, ['csrf_protection' => false]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $vevent = $event->getVObject();

            $calendarBackend->createCalendarObject($event->calendarid, $vevent->VEVENT->UID.'.ics', $vevent->serialize());

            $this->addFlash('success', "L'événement a bien été créé.");

            $rawEvent = $this->get('pmanager')->findById('public', 'calendarobject', $vevent->VEVENT->UID);

            return $this->redirectToRoute('event_read', ['slug' => $rawEvent->slug]);
        }

        return $this->render('browser/event_create.html.twig', array(
            'form' => $form->createView(),
        ));

        //return new Response("eventCreateAction");
    }

    /**
     * @param string $slug
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function eventReadAction($slug)
    {
        $where = Where::create('slug = $*', [$slug]);
        $rawEvents = $this->get('pmanager')->findWhere('public', 'calendarobject', $where);

        if ($rawEvents->count() == 0) {
            return $this->redirectToRoute('event_home');
        }

        $rawEvent = $rawEvents->get(0);

        $event = new Event();
        $event->loadFromCalData($rawEvent->calendarData);
        $event->slug = $slug;

        $calendar = $this->get('pmanager')->findById('public', 'calendar', $rawEvent->calendarid);

        $ownEvent = false;

        $tkn = $this->get('security.token_storage')->getToken();
        if (!$tkn instanceof AnonymousToken) {
            $usr = $tkn->getUser();
            $username = $usr->getUsernameCanonical();

            if ($calendar->principaluri == 'principals/'.$username) {
                $ownEvent = true;
            }
        }

        return $this->render('browser/event_read.html.twig', array(
            'event' => $event,
            'calendar' => $calendar,
            'ownEvent' => $ownEvent,
        ));
    }

    /**
     * @param Request $request
     * @param string  $slug
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function eventUpdateAction(Request $request, $slug)
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'Unable to access this page!');

        $usr = $this->get('security.token_storage')->getToken()->getUser();
        $username = $usr->getUsernameCanonical();

        $where = Where::create('slug = $*', [$slug]);
        $rawEvents = $this->get('pmanager')->findWhere('public', 'calendarobject', $where);

        if ($rawEvents->count() == 0) {
            return $this->redirectToRoute('event_home');
        }

        $rawEvent = $rawEvents->get(0);

        $event = new Event();
        $event->loadFromCalData($rawEvent->calendarData);

        $form = $this->createForm(new EventType(), $event, ['csrf_protection' => false]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $vevent = $event->getVObject();

            $calendarBackend = new Calendar($this->get('pmanager'), null, $this->get('slugify'));

            $calendarBackend->updateCalendarObject($rawEvent->calendarid, $rawEvent->uri, $vevent->serialize());

            $this->addFlash('success', "L'événement a bien été modifié.");

            $rawEvent = $this->get('pmanager')->findById('public', 'calendarobject', $event->id);

            return $this->redirectToRoute('event_read', ['slug' => $rawEvent->slug]);
        }

        return $this->render('browser/event_update.html.twig', array(
            'form' => $form->createView(),
            'slug' => $slug,
        ));
    }

    /**
     * @param string $slug
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function eventDeleteAction($slug)
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'Unable to access this page!');

        $usr = $this->get('security.token_storage')->getToken()->getUser();

        $where = Where::create('slug = $*', [$slug]);

        $events = $this->get('pmanager')->findWhere('public', 'calendarobject', $where);

        if ($events->count() == 0) {
            return $this->redirectToRoute('event_home');
        }

        $event = $events->get(0);

        $calendar = $this->get('pmanager')->findById('public', 'calendar', $event->calendarid);

        if ($calendar->principaluri != 'principals/'.$usr->getUsernameCanonical()) {
            $this->addFlash('danger', 'Cet événement ne fait pas parti de vos calendriers.');

            return $this->redirectToRoute('event_read', ['uri' => $slug]);
        }

        $calendarBackend = new Calendar($this->get('pmanager'));

        $calendarBackend->deleteCalendarObject($event->calendarid, $event->uri);

        $this->addFlash('success', 'L\'événement a bien été supprimé.');

        return $this->redirectToRoute('event_home');
    }

    /*          CALENDAR          */

    /**
     * @return Response
     */
    public function calendarHomeAction()
    {
        $calendarBackend = new Calendar($this->get('pmanager'));

        $tkn = $this->get('security.token_storage')->getToken();

        $calendars = $calendarBackend->getAllCalendars();

        $calendarsOthers = [];
        $calendarsUser = [];

        foreach ($calendars as $calendar) {
            $calendar->events = $calendarBackend->getCalendarObjects($calendar->uid);
            $calendar->user = substr($calendar->principalUri, 11);
        }

        if (!$tkn instanceof AnonymousToken) {
            // list all calendars + user's calendar
            $usr = $tkn->getUser();
            $username = $usr->getUsernameCanonical();

            foreach ($calendars as $key => $calendar) {
                if ($calendar->principalUri == 'principals/'.$username) {
                    $calendarsUser[] = $calendar;
                } else {
                    $calendarsOthers[] = $calendar;
                }
            }
        } else {
            foreach ($calendars as $calendar) {
                $calendarsOthers[] = $calendar;
            }
        }

        $calendarsUser = $this->sortByStringField($calendarsUser, 'displayname');
        $calendarsOthers = $this->sortByStringField($calendarsOthers, 'displayname');

        return $this->render('browser/calendar_home.html.twig', array(
            'calendars' => $calendarsOthers,
            'calendarsUser' => $calendarsUser,
        ));
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws AccessDeniedException
     */
    public function calendarCreateAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'Unable to access this page!');

        $usr = $this->get('security.token_storage')->getToken()->getUser();

        $form = $this->createForm(new CalendarType(), null, ['csrf_protection' => false]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $values = $form->getData();

            $calendarUri = $this->generateCalendarUri();

            $calendarBackend = new Calendar($this->get('pmanager'), null, $this->get('slugify'));

            $raw = [
                '{DAV:}displayname' => $values['displayname'],
                '{urn:ietf:params:xml:ns:caldav}calendar-description' => $values['description'],
            ];

            $principalUri = 'principals/'.$usr->getUsernameCanonical();

            $calendarBackend->createCalendar($principalUri, $calendarUri, $raw);

            $this->addFlash('success', 'Le calendrier "'.$values['displayname'].'" a bien été créé.');

            $where = Where::create('uri = $*', [$calendarUri]);
            $rawCalendars = $this->get('pmanager')->findWhere('public', 'calendar', $where);

            return $this->redirectToRoute('calendar_read', ['slug' => $rawCalendars->get(0)->slug]);
        }

        return $this->render('browser/calendar_create.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @param string $slug
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function calendarReadAction($slug)
    {
        $where = Where::create('slug = $*', [$slug]);

        $calendars = $this->get('pmanager')->findWhere('public', 'calendar', $where);

        if ($calendars->count() == 0) {
            return $this->redirectToRoute('calendar_home');
        }

        $calendar = $calendars->get(0);

        $ownCalendar = false;

        $tkn = $this->get('security.token_storage')->getToken();
        if (!$tkn instanceof AnonymousToken) {
            $usr = $tkn->getUser();
            $username = $usr->getUsernameCanonical();

            if ($calendar->principaluri == 'principals/'.$username) {
                $ownCalendar = true;
            }
        }

        $where = Where::create('calendarid = $*', [$calendar->uid]);

        $rawEvents = $this->get('pmanager')->findWhere('public', 'calendarobject', $where);

        $events = [];

        foreach ($rawEvents as $raw) {
            $event = new Event();

            foreach ($raw->extracted_data as $name => $value) {
                $event->__set($name, $value);
            }

            $event->slug = $raw->slug;

            $events[] = $event;
        }

        $exportUrl = $this->generateUrl('caldav', ['url' => 'calendars/'.substr($calendar->principaluri, 11).'/'.$calendar->uri, 'export' => '']);

        return $this->render('browser/calendar_read.html.twig', array(
            'calendar' => $calendar,
            'ownCalendar' => $ownCalendar,
            'events' => $events,
            'exportUrl' => $exportUrl,
        ));
    }

    /**
     * @param Request $request
     * @param string  $slug
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function calendarUpdateAction(Request $request, $slug)
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'Unable to access this page!');

        $usr = $this->get('security.token_storage')->getToken()->getUser();

        $where = Where::create('slug = $*', [$slug]);

        $calendars = $this->get('pmanager')->findWhere('public', 'calendar', $where);

        if ($calendars->count() == 0) {
            return $this->redirectToRoute('calendar_home');
        }

        $calendar = $calendars->get(0);

        if ($calendar->principaluri != 'principals/'.$usr->getUsernameCanonical()) {
            $this->addFlash('danger', 'Ce calendrier ne vous appartient pas.');

            return $this->redirectToRoute('calendar_read', ['slug' => $slug]);
        }

        $previousName = $calendar->displayname;

        $form = $this->createForm(new CalendarType(), $calendar, ['csrf_protection' => false]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($previousName != $calendar->displayname) {
                $calendarBackend = new Calendar($this->get('pmanager'), null, $this->get('slugify'));

                $calendar->slug = $calendarBackend->generateSlug($calendar->displayname, 'calendar');
            }

            $this->get('pmanager')->updateOne('public', 'calendar', $calendar, ['slug', 'displayname', 'description']);

            $this->addFlash('success', 'Le calendrier "'.$calendar->displayName.'" a bien été modifié.');

            return $this->redirectToRoute('calendar_read', ['slug' => $calendar->slug]);
        }

        return $this->render('browser/calendar_update.html.twig', array(
            'form' => $form->createView(),
            'slug' => $slug,
        ));
    }

    /**
     * @param string $slug
     *
     * @return RedirectResponse
     */
    public function calendarDeleteAction($slug)
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'Unable to access this page!');

        $usr = $this->get('security.token_storage')->getToken()->getUser();

        $where = Where::create('slug = $*', [$slug]);

        $calendars = $this->get('pmanager')->findWhere('public', 'calendar', $where);

        if ($calendars->count() == 0) {
            return $this->redirectToRoute('calendar_home');
        }

        $calendar = $calendars->get(0);

        if ($calendar->principaluri != 'principals/'.$usr->getUsernameCanonical()) {
            $this->addFlash('danger', 'Ce calendrier ne vous appartient pas.');

            return $this->redirectToRoute('calendar_read', ['slug' => $slug]);
        }

        $calendarBackend = new Calendar($this->get('pmanager'));

        $calendarBackend->deleteCalendar($calendar->uid);

        $this->addFlash('success', 'Le calendrier "'.$calendar->displayName.'" a bien été supprimé.');

        return $this->redirectToRoute('calendar_home');
    }

    /**
     * @return string
     * @link http://php.net/manual/fr/function.uniqid.php#94959
     */
    protected function generateCalendarUri()
    {
        return strtoupper(sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        ));
    }

    /**
     * @param array  $data
     * @param string $fieldName
     *
     * @return array
     */
    protected function sortByStringField($data, $fieldName)
    {
        usort($data, function ($a, $b) use ($fieldName) {
            return strcmp($a->$fieldName, $b->$fieldName);
        });

        return $data;
    }

    /**
     * @param array  $data
     * @param string $fieldName
     *
     * @return array
     */
    protected function sortByDateField($data, $fieldName)
    {
        usort($data, function ($a, $b) use ($fieldName) {
            return $a->$fieldName > $b->$fieldName;
        });

        return $data;
    }
}

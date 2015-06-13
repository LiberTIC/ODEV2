<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use PommProject\Foundation\Where;
use Sabre\VObject;

/**
 * Class APIController
 *
 * @package AppBundle\Controller
 */
class APIController extends Controller
{
    /**
     * @var array
     */
    public $acceptedMimeFormat = ['application/json','text/html','application/xml','text/csv'];

    /**
     * @return Response
     * @throws \Exception
     */
    public function indexAction()
    {
        $data = array(
            'ODEV2_api' => array(
                'version' => '0.1',
                'author' => 'Thibaud Courtoison',
                'links' => array(
                    ['rel' => 'github', 'href' => 'https://github.com/LiberTIC/ODEV2'],
                    ['rel' => 'documentation', 'href' => 'https://github.com/LiberTIC/ODEV2/blob/master/doc/RestAPI.md'],
                    ['rel' => 'list-of-calendars', 'href' => $this->generateUrl('api_calendar_list', [], true)],
                    ['rel' => 'list-of-events', 'href' => $this->generateUrl('api_event_list', [], true)],
                    ),
                ),
            );

        return $this->buildResponse($data);
    }

    /* CALENDAR ACTIONS */

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function indexCalendarAction()
    {
        return $this->redirectToRoute('api_calendar_list');
    }

    /**
     * @return Response
     * @throws \Exception
     */
    public function listCalendarAction()
    {
        $calendars = $this->get('pmanager')->findAll('public', 'calendar');

        $ret = [];
        foreach ($calendars as $calendar) {
            $ret[] = array(
                        'displayname' => $calendar->displayname,
                        'slug' => $calendar->slug,
                        'uri' => $calendar->uri,
                        'links' => array(
                                        ['rel' => 'self', 'href' => $this->generateUrl('api_calendar_get', array('uri' => $calendar->uri), true)],
                                        ['rel' => 'pretty_self', 'href' => $this->generateUrl('calendar_read', array('slug' => $calendar->slug), true)],
                                        ['rel' => 'events', 'href' => $this->generateUrl('api_calendar_event_list', array('uri' => $calendar->uri), true)],
                                    ),
                    );
        }

        return $this->buildResponse(['count' => count($calendars), 'calendars' => $ret]);
    }

    /**
     * @param string $uri
     *
     * @return Response
     * @throws \Exception
     */
    public function getCalendarAction($uri)
    {
        $where = Where::create('uri = $*', [$uri]);

        $calendars = $this->get('pmanager')->findWhere('public', 'calendar', $where);

        if ($calendars->count() == 0) {
            return $this->buildError('404', 'The calendar with the given uri could not be found.');
        }

        $calendar = $calendars->get(0)->extract();

        $ret = $this->get('pmanager')->query('SELECT COUNT(*) as count FROM calendarobject WHERE calendarid = '.$calendar['uid']);

        $calendar['total_events'] = $ret->fetchRow(0)['count'];

        $calendar['links'][] =
            ['rel' => 'self', 'href' => $this->generateUrl('api_calendar_get', array('uri' => $uri), true)];
        $calendar['links'][] =
            ['rel' => 'pretty_self', 'href' => $this->generateUrl('calendar_read', array('slug' => $calendar['slug']), true)];
        $calendar['links'][] =
            ['rel' => 'events', 'href' => $this->generateUrl('api_calendar_event_list', array('uri' => $uri), true)];

        return $this->buildResponse(['calendar' => $calendar]);
    }

    /**
     * @param string $uri
     *
     * @return Response
     * @throws \Exception
     */
    public function listCalendarEventAction($uri)
    {
        $where = Where::create('uri = $*', [$uri]);

        $calendars = $this->get('pmanager')->findWhere('public', 'calendar', $where);

        if ($calendars->count() == 0) {
            return $this->buildError('404', 'The calendar with the given uri could not be found.');
        }

        $calendar = $calendars->get(0);

        $where = Where::create('calendarid = $*', [$calendar->uid]);

        $events = $this->get('pmanager')->findWhere('public', 'calendarobject', $where);

        $ret = [];
        foreach ($events as $event) {
            $ret[] = array(
                        'name' => $event->extracted_data['name'],
                        'slug' => $event->slug,
                        'uri' => $event->uid,
                        'calendaruri' => $uri,
                        'etag' => $event->etag,
                        'links' => array(
                                        ['rel' => 'self', 'href' => $this->generateUrl('api_event_get', array('uriEvent' => $event->uid), true)],
                                        ['rel' => 'pretty_self', 'href' => $this->generateUrl('event_read', array('slug' => $event->slug), true)],
                                        ['rel' => 'calendar', 'href' => $this->generateUrl('api_calendar_get', array('uri' => $uri), true)],
                                    ),
                    );
        }

        return $this->buildResponse(['count' => $events->count(), 'events' => $ret]);
    }

    /* EVENT ACTIONS */

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function indexEventAction()
    {
        return $this->redirectToRoute('api_event_list');
    }

    /**
     * @return Response
     * @throws \Exception
     */
    public function listEventAction()
    {
        $events = $this->get('pmanager')->findAll('public', 'calendarobject');

        $ret = [];
        foreach ($events as $event) {
            $calendar = $this->get('pmanager')->findById('public', 'calendar', $event->calendarid);

            $ret[] = array(
                        'name' => $event->extracted_data['name'],
                        'uri' => $event->uid,
                        'calendaruri' => $calendar->uri,
                        'etag' => $event->etag,
                        'links' => array(
                                        ['rel' => 'self', 'href' => $this->generateUrl('api_event_get', array('uriEvent' => $event->uid), true)],
                                        ['rel' => 'pretty_self', 'href' => $this->generateUrl('event_read', array('slug' => $event->slug), true)],
                                        ['rel' => 'calendar', 'href' => $this->generateUrl('api_calendar_get', array('uri' => $calendar->uri), true)],
                                    ),
                    );
        }

        return $this->buildResponse(['count' => count($events), 'events' => $ret]);
    }

    /**
     * @param string $uriEvent
     *
     * @return Response
     * @throws \Exception
     */
    public function getEventAction($uriEvent)
    {
        $event = $this->get('pmanager')->findById('public', 'calendarobject', $uriEvent);

        if ($event == null) {
            return $this->buildError('404', 'The event with the given uri could not be found.');
        }

        $calendarData = $event->calendardata;
        $vobject = VObject\Reader::read($calendarData);

        $calendar = $this->get('pmanager')->findById('public', 'calendar', $event->calendarid);

        $links = array(
                ['rel' => 'self', 'href' => $this->generateUrl('api_event_get', array('uriEvent' => $uriEvent), true)],
                ['rel' => 'pretty_self', 'href' => $this->generateUrl('event_read', array('slug' => $event->slug), true)],
                ['rel' => 'calendar', 'href' => $this->generateUrl('api_calendar_get', array('uri' => $calendar->uri), true)],
            );

        $ret = [
                'name' => $event->extracted_data['name'],
                'slug' => $event->slug,
                'uri' => $uriEvent,
                'calendaruri' => $calendar->uri,
                'etag' => $event->etag,
                'links' => $links,
                'extracted_data' => $event->extracted_data,
                'jCal' => $vobject->jsonSerialize(),
            ];

        return $this->buildResponse(['event' => $ret]);
    }

    /* END */

    /**
     * @param mixed $data
     *
     * @return Response
     * @throws \Exception
     */
    public function buildResponse($data)
    {
        $format = 'json';
        $formats = $this->get('request')->getAcceptableContentTypes();
        foreach ($formats as $f) {
            if (in_array($f, $this->acceptedMimeFormat)) {
                $format = explode('/', $f)[1];
                break;
            }
        }

        $format = $format == 'html' ? 'json' : $format; // Set html behavior as json behavior

        if ($format == 'json') {
            $response = new Response(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        } else {
            throw new \Exception('Not Supported Yet.');

            // here, convert from json to whatever you like;
            // @todo @tofix: unreachable statement.
            return new Response($data);
        }
    }

    /**
     * @param int    $code
     * @param string $message
     *
     * @return Response
     * @throws \Exception
     */
    public function buildError($code, $message)
    {
        $error = ['error' => ['code' => $code, 'message' => $message]];

        return $this->buildResponse($error);
    }
}

<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use PommProject\Foundation\Where;
use Sabre\VObject;
use AppBundle\Backend\CalDAV\Calendar as CalendarBackend;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use AppBundle\Entity\Event;

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
     *
     * @ApiDoc(
     *  description="Index of the API"
     * )
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
     *
     * @ApiDoc(
     *  description="Redirect to /api/calendar/list"
     * )
     */
    public function indexCalendarAction()
    {
        return $this->redirectToRoute('api_calendar_list');
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @ApiDoc(
     *  description="Create a new calendar",
     *  requirements={
     *      {
     *          "name"="displayname",
     *          "dataType"="string",    
     *          "description"="The name of the calendar",
     *      },
     *      {
     *          "name"="username",
     *          "dataType"="string",
     *          "description"="The name of the owner of the calendar"
     *      }
     *  },
     *  parameters={
     *      {
     *          "name"="description",
     *          "dataType"="string",
     *          "required"=false,
     *          "description"="The description of the calendar"
     *      },
     *  }
     * )
     */
    public function createCalendarAction(Request $request)
    {

        $params = array();
        $content = $request->getContent();
        if (!empty($content))
        {
            $params = json_decode($content,true);
        }


        if (!isset($params['displayname']) || !isset($params['description']) || !isset($params['username'])) {
            return $this->buildError(400,'Missing parameters');
        }


        $calendarBackend = new CalendarBackend($this->get('pmanager'), null, $this->get('slugify'));

        $calendarUri = $calendarBackend->generateCalendarUri();

        $raw = [
            '{DAV:}displayname' => $params['displayname'],
            '{urn:ietf:params:xml:ns:caldav}calendar-description' => $params['description'] ?: "",
        ];

        $principalUri = 'principals/'.$params['username'];

        $calendarUid = $calendarBackend->createCalendar($principalUri, $calendarUri, $raw);

        return $this->buildResponse(['created' => $calendarUri]);
    }

    /**
     * @return Response
     * @throws \Exception
     *
     * @ApiDoc(
     *  description="List all calendars"
     * )
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
     *
     * @ApiDoc(
     *  description="Retrieve the calendar with the given uri",
     *  requirements={
     *      {
     *          "name"="uri",
     *          "dataType"="string",
     *          "description"="The uri of the calendar",
     *      }
     *  },
     * )
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
     * @param Request $request
     * @param string $uri
     * @return Response
     * @throws \Exception
     *
     * @ApiDoc(
     *  description="Update the calendar with the given uri",
     *  requirements={
     *      {
     *          "name"="uri",
     *          "dataType"="string",    
     *          "description"="The uri of the calendar",
     *      },
     *  },
     *  parameters={
     *      {
     *          "name"="displayname",
     *          "dataType"="string",
     *          "required"=false,
     *          "description"="The name of the calendar"
     *      },
     *      {
     *          "name"="description",
     *          "dataType"="string",
     *          "required"=false,
     *          "description"="The description of the calendar"
     *      }
     *  }
     * )
     */
    public function updateCalendarAction(Request $request, $uri)
    {
        $where = Where::create('uri = $*', [$uri]);

        $calendars = $this->get('pmanager')->findWhere('public', 'calendar', $where);

        if ($calendars->count() == 0) {
            return $this->buildError('404', 'The calendar with the given uri could not be found.');
        }

        $calendar = $calendars->get(0);

        $params = array();
        $content = $request->getContent();
        if (!empty($content))
        {
            $params = json_decode($content,true);
        }

        $previousName = $calendar->displayname;

        foreach ($params as $name => $value) {
            $calendar->$name = $value;
        }

        if ($previousName != $calendar->displayname) {
            $calendarBackend = new CalendarBackend($this->get('pmanager'), null, $this->get('slugify'));

            $calendar->slug = $calendarBackend->generateSlug($calendar->displayname, 'calendar');
        }

        $this->get('pmanager')->updateOne('public','calendar',$calendar,['slug','displayname','description']);

        return $this->buildResponse(['calendar' => 'updated']);
    }

    /**
     * @param string $uri
     * @return Response
     * @throws \Exception
     *
     * @ApiDoc(
     *  description="Delete the calendar with the given uri",
     *  requirements={
     *      {
     *          "name"="uri",
     *          "dataType"="string",
     *          "description"="The uri of the calendar",
     *      }
     *  },
     * )
     */
    public function deleteCalendarAction($uri)
    {
        $where = Where::create('uri = $*', [$uri]);

        $calendars = $this->get('pmanager')->findWhere('public', 'calendar', $where);

        if ($calendars->count() == 0) {
            return $this->buildError('404', 'The calendar with the given uri could not be found.');
        }

        $calendar = $calendars->get(0);

        $calendarBackend = new CalendarBackend($this->get('pmanager'));

        $calendarBackend->deleteCalendar($calendar->uid);

        return $this->buildResponse(['calendar' => 'deleted']);
    }

    /**
     * @param string $uri
     *
     * @return Response
     * @throws \Exception
     *
     * @ApiDoc(
     *  description="List all events of a calendar",
     *  requirements={
     *      {
     *          "name"="uri",
     *          "dataType"="string",
     *          "description"="The uri of the calendar",
     *      }
     *  },
     * )
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
     *
     * @ApiDoc(
     *  description="Redirect to /api/event/list"
     * )
     */
    public function indexEventAction()
    {
        return $this->redirectToRoute('api_event_list');
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Exception
     *
     * @ApiDoc(
     *  description="Create a new event",
     *  requirements={
     *      {
     *          "name"="calendar_uri",
     *          "dataType"="string",    
     *          "description"="The uri of the calendar",
     *      },
     *      {
     *          "name"="event_data",
     *          "dataType"="array",
     *          "description"="All the fields of the new event"
     *      }
     *  }
     * )
     */
    public function createEventAction(Request $request)
    {
        $params = array();
        $content = $request->getContent();
        if (!empty($content))
        {
            $params = json_decode($content,true);
        }

        if (!isset($params['calendar_uri']) || !isset($params['event_data'])) {
            return $this->buildError(400,'Missing parameters');
        }

        $calendarBackend = new CalendarBackend($this->get('pmanager'), $this->generateUrl('event_read', [], true), $this->get('slugify'));

        $calendarUri = $params['calendar_uri'];

        $where = Where::create('uri = $*',[$calendarUri]);
        $rawCalendars = $this->get('pmanager')->findWhere('public','calendar',$where);

        if ($rawCalendars->count() == 0) {
            return $this->buildError('400','CalendarUri given does not correspond to any calendar in the database');
        }

        $calendar = $rawCalendars->get(0);

        $calendarId = $calendar->uid;

        $event = new Event();

        foreach($params['event_data'] as $name => $value) {
            $event->$name = $value;
        }

        $vevent = $event->getVObject();

        $calendarBackend->createCalendarObject($calendarId,$vevent->VEVENT->UID.'.ics',$vevent->serialize());

        return $this->buildResponse(['created' => $vevent->VEVENT->UID->getValue()]);
    }

    /**
     * @return Response
     * @throws \Exception
     *
     * @ApiDoc(
     *  description="List all events"
     * )
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
     *
     * @ApiDoc(
     *  description="Retrieve the event with the given uri",
     *  requirements={
     *      {
     *          "name"="uriEvent",
     *          "dataType"="string",
     *          "description"="The uri of the event",
     *      }
     *  },
     * )
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

    /**
     * @param Request $request
     * @param string $uriEvent
     * @return Response
     * @throws \Exception
     *
     * @ApiDoc(
     *  description="Update the event with the given uri",
     *  requirements={
     *      {
     *          "name"="uriEvent",
     *          "dataType"="string",
     *          "description"="The uri of the event",
     *      }
     *  },
     *  parameters={
     *      {
     *          "name"="some_property",
     *          "dataType"="string",
     *          "required"=false,
     *          "description"="Some property to update"
     *      }
     *  }
     * )
     */
    public function updateEventAction(Request $request, $uriEvent)
    {
        $rawEvent = $this->get('pmanager')->findById('public', 'calendarobject', $uriEvent);

        if ($rawEvent == null) {
            return $this->buildError('404', 'The event with the given uri could not be found.');
        }

        $params = array();
        $content = $request->getContent();
        if (!empty($content))
        {
            $params = json_decode($content,true);
        }

        $event = new Event();
        $event->loadFromCalData($rawEvent->calendarData);

        foreach ($params as $name => $value) {
            $event->__set($name,$value);
        }

        $calendarBackend = new CalendarBackend($this->get('pmanager'), $this->generateUrl('event_read', [], true), $this->get('slugify'));

        $calendarBackend->updateCalendarObject($rawEvent->calendarid,$rawEvent->uri,$event->getVObject()->serialize());

        return $this->buildResponse(['event' => 'updated']);
    }

    /**
     * @param string $uriEvent
     *
     * @return Response
     * @throws \Exception
     *
     * @ApiDoc(
     *  description="Delete the event with the given uri",
     *  requirements={
     *      {
     *          "name"="uriEvent",
     *          "dataType"="string",
     *          "description"="The uri of the event",
     *      }
     *  },
     * )
     */
    public function deleteEventAction($uriEvent)
    {
        $event = $this->get('pmanager')->findById('public', 'calendarobject', $uriEvent);

        if ($event == null) {
            return $this->buildError('404', 'The event with the given uri could not be found.');
        }

        $calendarBackend = new CalendarBackend($this->get('pmanager'));

        $calendarBackend->deleteCalendarObject($event->calendarid, $event->uri);

        return $this->buildResponse(['event' => 'deleted']);
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

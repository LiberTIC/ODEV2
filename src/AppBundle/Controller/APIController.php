<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class APIController extends Controller
{
    public $acceptedMimeFormat = ['application/json','text/html','application/xml','text/csv'];

    public function indexAction()
    {
        $data = array(
            'ODEV2_api' => array(
                'version' => '0.1', 
                'author' => 'Thibaud Courtoison',
                'links' => array(
                    ['rel' => 'github', 'href' => 'https://github.com/LiberTIC/ODEV2'],
                    ['rel' => 'documentation', 'href' => 'https://github.com/LiberTIC/ODEV2/blob/master/doc/RestAPI.md'],
                    ['rel' => 'listOfCalendars', 'href' => $this->generateUrl('api_calendar_list',[],true)]
                    )
                )
            );

        return $this->buildResponse($data);
    }


    /* CALENDAR ACTIONS */

    public function indexCalendarAction()
    {
        return $this->redirectToRoute('api_calendar_list');
    }

    public function listCalendarAction()
    {
        $calendars = $this->get('esmanager')->simpleSearch('caldav','calendars');

        $ret = [];
        foreach($calendars as $calendar)
        {
            $ret[] = array(
                        'uri' => $calendar['_source']['uri'], 
                        'displayname' => $calendar['_source']['displayname'],
                        'links' => array(
                                        ['rel' => 'self', 'href' => $this->generateUrl('api_calendar_get',array('uri' => $calendar['_source']['uri']),true)],
                                        //['rel' => 'events', 'href' => $this->generateUrl('api_calendar_event_list',array('uri' => $calendar['_source']['uri']),true)]
                                    )
                    );
        }

        return $this->buildResponse(['count' => count($calendars), 'calendars' => $ret]);
    }

    public function getCalendarAction($uri)
    {
        $calendar = $this->get('esmanager')->simpleQuery('caldav','calendars',['uri' => $uri]);

        if ($calendar == null)
        {
            return $this->buildError('404','The calendar with the given uri could not be found.');
        }

        $ret = [];

        $attr = ['displayname','uri','synctoken','description'];

        foreach($attr as $a) $ret['calendar'][$a] = $calendar[0]['_source'][$a];

        $ret['calendar']['links'][] = 
            ["rel" => "self", "href" => $this->generateUrl('api_calendar_get',array('uri' => $uri),true)];
        $ret['calendar']['links'][] = 
            ["rel" => "events", "href" => $this->generateUrl('api_calendar_event_list',array('uri' => $uri),true)];
        $ret['calendar']['links'][] = ["rel" => "owner", "href" => "not implemented yet"];
        /*$ret['calendar']['events']['total'] = 0;

        $events = $this->get('esmanager')->simpleQuery('caldav','calendarobjects', ['calendarid' => $calendar[0]['_source']['id']]);

        if ($events != null)
        {
            $ret['calendar']['events']['links'] = [];
            $nb = 0;
            foreach($events as $event)
            {
                $nb++;
                $ret['calendar']['events']['links'][] = 
                    [ 
                      'rel' => 'event',
                      'href' => $this->generateUrl('api_calendar_event_get',array('uri' => $uri, 'uriEvent' => $event['_source']['uid']))
                    ];
            }

            $ret['calendar']['events']['total'] = $nb;
        }*/

        return $this->buildResponse($ret);
    }


    /* EVENT ACTIONS */


    public function indexCalendarEventAction($uri)
    {
        return $this->redirectToRoute('api_calendar_event_list',['uri' => $uri]);
    }

    public function listCalendarEventAction($uri)
    {
        $calendar = $this->get('esmanager')->simpleQuery('caldav','calendars',['uri' => $uri]);

        if ($calendar == null)
        {
            return $this->buildError('404','The calendar with the given uri could not be found.');
        }

        $calendarId = $calendar[0]['_id'];
        $events = $this->get('esmanager')->simpleQuery('caldav','calendarobjects', ['calendarid' => $calendarId]);
    
        $ret = [];
        foreach($events as $event) {
            $ret[] = array(
                        'uri' => $event['_source']['uid'], 
                        'calendaruri' => $uri, 
                        'etag' => $event['_source']['etag'],
                        'links' => array(
                                        ['rel' => 'self', 'href' => $this->generateUrl('api_event_get',array('uriEvent' => $event['_source']['uid']),true)],
                                        ['rel' => 'calendar', 'href' => $this->generateUrl('api_calendar_get',array('uri' => $uri),true)],
                                    )
                    );
        }

        return $this->buildResponse(['count' => count($events), 'events' => $ret]);
    }

    public function getEventAction($uriEvent)
    {
        $event = $this->get('esmanager')->simpleQuery('caldav','calendarobjects',['uid' => $uriEvent/*, 'calendarid' => $calendarId*/]);
        // Well, in fact, we use the uid, not the uri

        if ($event == null)
        {
            return $this->buildError('404','The event with the given uri could not be found.');
        }

        $vobject = $event[0]['_source']['vobject'];
        $vobject = $this->get('converter')->jCalUnfix($vobject);

        $calendar = $this->get('esmanager')->simpleGet('caldav','calendars',$event[0]['_source']['calendarid']);

        $uri = null;
        if ($calendar != null)
        {
            $uri = $calendar['_source']['uri'];
        }

        $links = array(
                ['rel' => 'self', 'href' => $this->generateUrl('api_event_get',array('uriEvent' => $uriEvent),true)],
                ['rel' => 'calendar', 'href' => $this->generateUrl('api_calendar_get',array('uri' => $uri),true)],
            );

        $ret = array(
                'uri' => $uriEvent, 
                'calendaruri' => $uri, 
                'etag' => $event[0]['_source']['etag'], 
                'links' => $links,
                'vobject' => $vobject);

        return $this->buildResponse(['event' => $ret ]);
    }


    /* END */


    public function buildResponse($data) {

        $format = 'json';
        $formats = $this->get('request')->getAcceptableContentTypes();
        foreach($formats as $f) {
            if (in_array($f, $this->acceptedMimeFormat)) {
                $format = explode("/",$f)[1];
                break;
            }
        }

        $format = $format == 'html' ? 'json' : $format; // Set html behavior as json behavior

        if ($format == 'json' ) {
            $response = new Response(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        } else {
            $data = $this->container->get('converter')->convert('json',$format,$data);

            return new Response($data);
        }
    }

    public function buildError($code,$message) {

        $error = ['error' => ['code' => $code, 'message' => $message]];

        return $this->buildResponse($error);
    }
}
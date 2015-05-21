<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class APIController extends Controller
{
    public $acceptedMimeFormat = ['application/json','text/html','application/xml','text/csv'];

    public function indexAction()
    {
        $data = array('ODEV2_api' => array('version' => '0.1', 'author' => 'Thibaud Courtoison'));

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
            $ret[] = array('uri' => $calendar['_source']['uri'], 'displayname' => $calendar['_source']['displayname']);
        }

        return $this->buildResponse(['calendars' => $ret]);
    }

    public function getCalendarAction($uri)
    {
        $calendar = $this->get('esmanager')->simpleQuery('caldav','calendars',['uri' => $uri]);

        if ($calendar == null)
        {
            return $this->buildError('404','The calendar with the given uri could not be found.');
        }

        return $this->buildResponse(['calendar' => $calendar[0]['_source']]);
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

        $calendarId = $calendar[0]['_source']['id'];
        $events = $this->get('esmanager')->simpleQuery('caldav','calendarobjects', ['calendarid' => $calendarId]);
    
        $ret = [];
        foreach($events as $event) {
            $ret[] = ['uri' => $event['_source']['uri'], 'calendaruri' => $uri, 'etag' => $event['_source']['etag'] ];
        }

        return $this->buildResponse(['events' => $ret]);
    }

    public function getCalendarEventAction($uri,$uriEvent)
    {
        $calendar = $this->get('esmanager')->simpleQuery('caldav','calendars',['uri' => $uri]);

        if ($calendar == null)
        {
            return $this->buildError('404','The calendar with the given uri could not be found.');
        }

        $calendarId = $calendar[0]['_source']['id'];

        $event = $this->get('esmanager')->simpleQuery('caldav','calendarobjects',['uri' => $uriEvent, 'calendarid' => $calendarId]);

        if ($event == null)
        {
            return $this->buildError('404','The event with the given uri could not be found.');
        }

        $vobject = $event[0]['_source']['vobject'];
        $vobject = $this->get('converter')->jCalUnfix($vobject);
        return $this->buildResponse(['event' => ['uri' => $uriEvent, 'etag' => $event[0]['_source']['etag'], 'vobject' => $vobject ] ]);
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
            $response = new Response(json_encode($data, JSON_PRETTY_PRINT));
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
<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use AppBundle\Backend\ESManager;
use Elasticsearch;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render(':default:index.html.twig');
    }

    public function testAction()
    {
        $params = array();
        $params['connectionParams']['auth'] = array(
            'ODE',
            'ultraSecretePasswordOfTheDead',
            'Basic',
        );
        $client = new Elasticsearch\Client($params);
        $manager = new ESManager($client);
        $event = $manager->simpleGet('calendarobjects',13)['_source'];


        $converter = $this->container->get('converter');

        $data = <<<VCF
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Apple Inc.//Mac OS X 10.9.5//EN
CALSCALE:GREGORIAN
BEGIN:VTIMEZONE
TZID:Europe/Paris
BEGIN:DAYLIGHT
TZOFFSETFROM:+0100
RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU
DTSTART:19810329T020000
TZNAME:UTC+2
TZOFFSETTO:+0200
END:DAYLIGHT
BEGIN:STANDARD
TZOFFSETFROM:+0200
RRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU
DTSTART:19961027T030000
TZNAME:UTC+1
TZOFFSETTO:+0100
END:STANDARD
END:VTIMEZONE
BEGIN:VEVENT
CREATED:20150520T075724Z
UID:0494770F-374C-4AA8-9920-83CFDCE79E0E
DTEND;TZID=Europe/Paris:20150521T130000
TRANSP:OPAQUE
SUMMARY:Nouvel événement
DTSTART;TZID=Europe/Paris:20150521T120000
DTSTAMP:20150520T075809Z
SEQUENCE:2
URL;VALUE=URI:projet-ode.fr/event/170
END:VEVENT
END:VCALENDAR

VCF;

        

        $data = $converter->convert('icalendar','json',$data);

        $event['vobject'] = $data;
        $manager->simpleIndex('calendarobjects', 13, $event);


        //$data = $converter->convert('icalendar','json',$event['calendardata']);

        //$data = $converter->convert('icalendar','json',$data);
        //$response = new Response($data->serialize());

        $response = new Response(json_encode($data, JSON_PRETTY_PRINT));
        //$response = new Response(implode(',',array_keys($event)));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

}

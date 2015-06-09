<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Sabre\VObject;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render(':default:index.html.twig');
    }

    public function testAction()
    {

        /*$calendars = $this->get('pomm')['caldav']
            ->getModel('\AppBundle\Model\Ode\PublicSchema\CalendarModel')
            ->findWhere('principaluri = $*', ['principal/admin']);*/

        /*$calendars = $this->get('pmanager')->findAll('public','calendar');

        foreach($calendars as $calendar) {
            print_r($calendar);
            //echo $calendar->displayname;
            echo "<br/><br/>";
        }

        

        return new Response();*/


        /*$manager = $this->get('esmanager');
        $event = $manager->simpleGet('caldav','calendarobjects',28)['_source'];*/


        $converter = $this->container->get('converter');

        $data = <<<VCF
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Apple Inc.//Mac OS X 10.9.5//EN
CALSCALE:GREGORIAN
BEGIN:VEVENT
CREATED:20150608T090900Z
UID:1B805B5B-F8B8-4665-AF09-C6E46AF95060
DTEND;VALUE=DATE:20150620
TRANSP:TRANSPARENT
SUMMARY:Tryc
DTSTART;VALUE=DATE:20150619
DTSTAMP:20150608T090922Z
SEQUENCE:2
URL;VALUE=URI:projet-ode.fr/event/014f03818c0782c4f8183d4e299ee05c
END:VEVENT
END:VCALENDAR

VCF;

        $vCal = VObject\Reader::read($data);

        //$data = $converter->convert('icalendar','json',$data);

        $data = $converter->extractToLobject($vCal);

        //$event['vobject'] = $data;
        //$manager->simpleIndex('calendarobjects', 13, $event);


        //$data = $converter->convert('icalendar','json',$event['calendardata']);

        //$data = $converter->convert('icalendar','json',$data);
        //$response = new Response($data->serialize());

        $response = new Response(json_encode($data, JSON_PRETTY_PRINT));
        //$response = new Response(implode(',',array_keys($event)));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

}

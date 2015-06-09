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

        /*$calendar = [
            'principaluri' => 'principals/admin',
            'displayname' => 'Sans titre',
            'uri' => 'D693B5E6-0EC2-4E58-AB17-E796E2A99C3A',
            'synctoken' => 1,
            'description' => 'Nouveau Calendrier',
            'calendarorder' => 1,
            'components' => ['VEVENT'],
            'transparent' => null,
            'timezone' => ' BEGIN:VCALENDAR
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
END:VCALENDAR
',
            'calendarorder' => 2,
            'calendarcolor' => '#0E61B9FF'
        ];


        $cal = $this->get('pmanager')->insertOne('public','calendar',$calendar);

        print_r($cal);

        return new Response();*/

        $newValues = [
            'displayname' => "Ouhlala"
        ];

        $calendar = $this->get('pmanager')->findById('public','calendar',5);

        foreach($newValues as $name => $value) {
            $calendar->$name = $value;
        }

        $this->get('pmanager')->updateOne('public','calendar',$calendar,array_keys($newValues));

        return new Response();

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


        /*$converter = $this->container->get('converter');

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

        return $response;*/
    }

}

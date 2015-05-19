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
        $event = $manager->simpleGet('calendarobjects',15)['_source'];


        $converter = $this->container->get('converter');
        //$data = $converter->convert('iCalendar','json', "BEGIN:VCALENDAR\nVERSION:2.0\nPRODID:-//Apple Inc.//Mac OS X 10.9.5//EN\nCALSCALE:GREGORIAN\nBEGIN:VEVENT\nCREATED:20150506T115630Z\nUID:FE9ACE3E-7FD4-4BD7-928A-C07F184232DB\nDTEND;VALUE=DATE:20150509\nTRANSP:TRANSPARENT\nSUMMARY:Woodstock 2\nDTSTART;VALUE=DATE:20150508\nDTSTAMP:20150506T115642Z\nSEQUENCE:2\nBEGIN:VALARM\nX-WR-ALARMUID:0A14D05F-6AE9-46B4-950E-21FF44988966\nUID:0A14D05F-6AE9-46B4-950E-21FF44988966\nTRIGGER:-PT15H\nX-APPLE-DEFAULT-ALARM:TRUE\nATTACH;VALUE=URI:Basso\nACTION:AUDIO\nEND:VALARM\nEND:VEVENT\nEND:VCALENDAR");
        //$data = $converter->convert('iCalendar','JSON', "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Apple Inc.//Mac OS X 10.9.5//EN\r\nCALSCALE:GREGORIAN\r\nBEGIN:VEVENT\r\nCREATED:20150513T122332Z\r\nUID:A9053998-F067-4CFB-A772-3F13CAA6F543\r\nDTEND;VALUE=DATE:20150514\r\nTRANSP:TRANSPARENT\r\nSUMMARY:Nouvel événement\r\nDTSTART;VALUE=DATE:20150513\r\nDTSTAMP:20150513T122458Z\r\nLOCATION:Nantes Commerce\\n3 Rue de Gorges\\n44000 Nantes\r\nX-APPLE-STRUCTURED-LOCATION;VALUE=URI;X-ADDRESS=3 Rue de Gorges\\\\n44000 \r\n Nantes;X-TITLE=Nantes Commerce:geo:47.213872,-1.558239\r\nSEQUENCE:2\r\nURL;VALUE=URI:jaifaiiiim.com\r\nBEGIN:VALARM\r\nX-WR-ALARMUID:A451E945-F044-45D3-AD75-E59FA8E90937\r\nUID:A451E945-F044-45D3-AD75-E59FA8E90937\r\nTRIGGER:-PT15H\r\nATTACH;VALUE=URI:Basso\r\nACTION:AUDIO\r\nX-APPLE-DEFAULT-ALARM:TRUE\r\nACKNOWLEDGED:20150513T122332Z\r\nEND:VALARM\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n");
        //$data = $converter->convert('icalendar','json',"BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Apple Inc.//Mac OS X 10.9.5//EN\r\nCALSCALE:GREGORIAN\r\nBEGIN:VEVENT\r\nCREATED:20150518T113226Z\r\nUID:90787FAD-A55E-45FD-9EEB-A8F97B760F39\r\nDTEND;VALUE=DATE:20150520\r\nTRANSP:TRANSPARENT\r\nSUMMARY:Machin chose\r\nDTSTART;VALUE=DATE:20150519\r\nDTSTAMP:20150518T113726Z\r\nLOCATION:Château de Saumur\\nRue d'Anjou\\n49400 Saumur\r\nX-APPLE-STRUCTURED-LOCATION;VALUE=URI;X-ADDRESS=Rue d'Anjou\\\\n49400 Saum\r\n ur;X-TITLE=Château de Saumur:geo:47.256467,-0.073061\r\nSEQUENCE:3\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n");
        //$vCal = \Sabre\VObject\Reader::read("BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Apple Inc.//Mac OS X 10.9.5//EN\r\nCALSCALE:GREGORIAN\r\nBEGIN:VEVENT\r\nCREATED:20150518T113226Z\r\nUID:90787FAD-A55E-45FD-9EEB-A8F97B760F39\r\nDTEND;VALUE=DATE:20150520\r\nTRANSP:TRANSPARENT\r\nSUMMARY:Machin chose\r\nDTSTART;VALUE=DATE:20150519\r\nDTSTAMP:20150518T113726Z\r\nLOCATION:Château de Saumur\\nRue d'Anjou\\n49400 Saumur\r\nX-APPLE-STRUCTURED-LOCATION;VALUE=URI;X-ADDRESS=Rue d'Anjou\\\\n49400 Saum\r\n ur;X-TITLE=Château de Saumur:geo:47.256467,-0.073061\r\nSEQUENCE:3\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n");
        //$data = 

        //$uid = $vCal->VEVENT->UID->__toString();
        //$data = $converter->convert('icalendar','json',"BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Apple Inc.//Mac OS X 10.9.5//EN\r\nCALSCALE:GREGORIAN\r\nBEGIN:VEVENT\r\nCREATED:20150518T113226Z\r\nUID:90787FAD-A55E-45FD-9EEB-A8F97B760F39\r\nDTEND;VALUE=DATE:20150520\r\nTRANSP:TRANSPARENT\r\nSUMMARY:Machin chose\r\nDTSTART;VALUE=DATE:20150519\r\nDTSTAMP:20150518T113726Z\r\nLOCATION:Château de Saumur\\nRue d'Anjou\\n49400 Saumur\r\nX-APPLE-STRUCTURED-LOCATION;VALUE=URI;X-ADDRESS=Rue d'Anjou\\\\n49400 Saum\r\n ur;X-TITLE=Château de Saumur:geo:47.256467,-0.073061\r\nSEQUENCE:3\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n");
        //return new Response($uid);

        //$data = $converter->convert('json','iCalendar',$event['vobject'])->jsonSerialize();

        //$vcal = \Sabre\VObject\Reader::read($event['vobject']);

        $data = $converter->convert('json','icalendar',$event['vobject']);

        //$data = $converter->convert('icalendar','json',$data);
        $response = new Response($data->serialize());

        //$response = new Response(json_encode($data, JSON_PRETTY_PRINT));
        //$response = new Response(implode(',',array_keys($event)));
        //$response->headers->set('Content-Type', 'application/json');

        return $response;







        // THERE IS SOMETHING ABOUT THE ORDER OF THE X-APPLE-STRUCTURED-LOCATION PROPERTIES THINGS
    }

}

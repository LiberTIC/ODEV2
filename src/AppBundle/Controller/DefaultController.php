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

        $data = $converter->convert('json','icalendar',$event['vobject']);

        //$data = $converter->convert('icalendar','json',$data);
        $response = new Response($data->serialize());

        //$response = new Response(json_encode($data, JSON_PRETTY_PRINT));
        //$response = new Response(implode(',',array_keys($event)));
        //$response->headers->set('Content-Type', 'application/json');

        return $response;
    }

}

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
}
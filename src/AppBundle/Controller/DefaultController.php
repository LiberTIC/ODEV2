<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render(':default:index.html.twig');
    }

    public function testAction()
    {
        $this->get('converter')->convert('icalendar', 'json', null);

        return new Response('');
    }
}

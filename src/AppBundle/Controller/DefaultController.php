<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function indexAction()
    {
    	if (true === $this->container->get('security.context')->isGranted('ROLE_USER')) {
            return new Response("Oh yisss");
        } else {
        	return new Response("Not connected");
        }

        return $this->render(':default:index.html.twig');
    }
}

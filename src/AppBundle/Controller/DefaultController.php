<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultController
 *
 * @package AppBundle\Controller
 */
class DefaultController extends Controller
{
    /**
     * @return Response
     */
    public function indexAction()
    {
        return $this->render(':default:index.html.twig');
    }

    /**
     * @return Response
     */
    public function testAction()
    {
        // @todo @tofix: missing converter service?
        $this->get('converter')->convert('icalendar', 'json', null);

        return new Response('');
    }
}

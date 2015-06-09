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

        /*$this->get('pmanager')->query('DELETE FROM calendarchange WHERE calendarid = ?',[9]);

        $this->get('pmanager')->query('DELETE FROM calendarobject WHERE calendarid = ?',[9]);

        $this->get('pmanager')->query('DELETE FROM calendar WHERE uid = ?',[9]);*/
    
        return new Response('');
    }

}

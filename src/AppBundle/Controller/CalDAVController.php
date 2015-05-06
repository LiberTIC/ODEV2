<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use PDO;
use Sabre;

class CalDAVController extends Controller
{
    public function indexAction(Request $request)
    {
        //return $this->render('caldav/index.html.twig');

        date_default_timezone_set('Europe/Paris');

        $baseUri = '/caldav/';

        $pdo = new PDO('mysql:dbname=sabredav;host=127.0.0.1', 'root', '');

        /*function exception_error_handler($errno, $errstr, $errfile, $errline ) {
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        }
        set_error_handler("exception_error_handler");*/

        #Backends
        $authBackend = new Sabre\DAV\Auth\Backend\PDO($pdo);
        $calendarBackend = new Sabre\CalDAV\Backend\PDO($pdo);
        $principalBackend = new Sabre\DAVACL\PrincipalBackend\PDO($pdo);

        $tree = [
            new Sabre\CalDAV\Principal\Collection($principalBackend),
            new Sabre\CalDAV\CalendarRoot($principalBackend, $calendarBackend),
        ];
        $server = new Sabre\DAV\Server($tree);
        $server->setBaseUri($baseUri);

        $authPlugin = new Sabre\DAV\Auth\Plugin($authBackend, 'SabreDAV');
        $server->addPlugin($authPlugin);
        $aclPlugin = new Sabre\DAVACL\Plugin();
        $server->addPlugin($aclPlugin);

        $caldavPlugin = new Sabre\CalDAV\Plugin();
        $server->addPlugin($caldavPlugin);

        $server->addPlugin(
            new Sabre\CalDAV\Subscriptions\Plugin()
        );

        $server->addPlugin(
            new Sabre\CalDAV\Schedule\Plugin()
        );

        $server->addPlugin(new Sabre\DAV\Sync\Plugin());

        $browser = new Sabre\DAV\Browser\Plugin();
        $server->addPlugin($browser);

        $server->exec();
        $server->httpResponse->setHeader('Content-Security-Policy', "allow 'self';");

        $this->logIt($request, $server->httpResponse);

        return new Response($server->httpResponse->getBody(), $server->httpResponse->getStatus(), $server->httpResponse->getHeaders());
    }

    private function logIt($request, $response)
    {
        $this->get('logger')->info('------------------------ METHOD -------------------------');
        $this->get('logger')->info($request->getMethod());
        $this->get('logger')->info('------------------------ REQUEST ------------------------');
        foreach ($request->headers->all() as $key => $value) {
            if (is_array($value)) {
                $this->get('logger')->info($key.' => '.implode(', ', $value));
            } else {
                $this->get('logger')->info($key.' => '.$value);
            }
        }
        $this->get('logger')->info('------------------------ RESPONSE -----------------------');
        $this->get('logger')->info($response->getBody());
        $this->get('logger')->info('------------------------ END ----------------------------');
    }
}

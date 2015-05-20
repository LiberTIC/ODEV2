<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sabre;
use AppBundle;

class CalDAVController extends Controller
{
    public function indexAction(Request $request)
    {
        date_default_timezone_set('Europe/Paris');

        $baseUri = '/caldav/';

        $manager = $this->get('esmanager');

        #Backends
        $authBackend = new AppBundle\Backend\CalDAV\Auth($manager);
        $calendarBackend = new AppBundle\Backend\CalDAV\Calendar($manager,$this->get('converter'));
        $principalBackend = new AppBundle\Backend\CalDAV\Principals($manager);

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

        $responseBody = $server->httpResponse->getBodyAsString(); // This method must be called only one time!

        $this->logIt($request, $server->httpResponse,$responseBody);

        return new Response($responseBody, $server->httpResponse->getStatus(), $server->httpResponse->getHeaders());
    }

    private function logIt($request, $response, $responseBody)
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
        $this->get('logger')->info($response->__toString());
        $this->get('logger')->info($responseBody);
        $this->get('logger')->info('------------------------ END ----------------------------');
    }
}

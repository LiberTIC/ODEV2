<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sabre;
use AppBundle;

class CalDAVController extends Controller
{
    public function indexAction(Request $request)
    {
        date_default_timezone_set('Europe/Paris');

        //$baseUri = '/caldav';
        $baseUri = '/ODEV2/web/app_dev.php/caldav';

        //$manager = $this->get('esmanager');

        $pmanager = $this->get('pmanager');

        //$pdo = new \PDO('mysql:dbname=sabredav;host=127.0.0.1','root','');

        #Backends
        $authBackend = new AppBundle\Backend\CalDAV\Auth($manager);
        //$calendarBackend = new AppBundle\Backend\CalDAV\CalendarOld($manager,$this->get('converter'));
        $calendarBackend = new AppBundle\Backend\CalDAV\Calendar($pmanager,$this->get('converter'));
        //$calendarBackend = new \Sabre\CalDAV\Backend\PDO($pdo);
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

        $callback = function () use ($server,$request) {

            /* These two lines fix a weird bug
               where SabreDAV would give the correct answer to a propfind */
            $url = $server->httpRequest->getUrl();
            $server->httpRequest = new Sabre\HTTP\Request($request->getMethod(),$url,$request->headers->all(),$request->getContent());

            $server->exec();

            /* These two lines log the request and the response */
            $responseBody = $server->httpResponse->getBodyAsString();
            $this->logIt($request, $server->httpResponse,$responseBody);
        };

        return new StreamedResponse($callback);
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

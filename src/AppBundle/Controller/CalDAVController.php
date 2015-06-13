<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sabre\HTTP\Response as SabreResponse;
use Sabre\HTTP\Request as SabreRequest;
use Sabre\CalDAV\CalendarRoot;
use Sabre\CalDAV\Principal\Collection;
use Sabre\DAV\Server;
// Plugins:
use Sabre\DAV\Auth\Plugin as AuthPlugin;
use Sabre\DAVACL\Plugin as ACLPlugin;
use Sabre\CalDAV\ICSExportPlugin;
use Sabre\CalDAV\Plugin as CalDAVPlugin;
use Sabre\CalDAV\Subscriptions\Plugin as SubscriptionsPlugin;
use Sabre\CalDAV\Schedule\Plugin as SchedulePlugin;
use Sabre\DAV\Sync\Plugin as SyncPlugin;
use Sabre\DAV\Browser\Plugin as BrowserPlugin;

use AppBundle\Backend\CalDAV\Auth;
use AppBundle\Backend\CalDAV\Calendar;
use AppBundle\Backend\CalDAV\Principals;

/**
 * Class CalDAVController
 *
 * @package AppBundle\Controller
 */
class CalDAVController extends Controller
{
    /**
     * @param Request $request
     *
     * @return StreamedResponse
     */
    public function indexAction(Request $request)
    {
        date_default_timezone_set('Europe/Paris');

        $baseUri = $this->generateUrl('caldav');

        $pmanager = $this->get('pmanager');

        // Backends:
        $authBackend = new Auth($pmanager);
        $calendarBackend = new Calendar(
            $pmanager, $this->generateUrl('event_read', [], true),
            $this->get('cocur_slugify')
        );
        $principalBackend = new Principals($pmanager);

        $tree = [
            new Collection($principalBackend),
            new CalendarRoot($principalBackend, $calendarBackend),
        ];
        $server = new Server($tree);
        $server->setBaseUri($baseUri);

        $server->addPlugin(new AuthPlugin($authBackend, 'SabreDAV'));
        $server->addPlugin(new ACLPlugin());
        $server->addPlugin(new ICSExportPlugin());
        $server->addPlugin(new CalDAVPlugin());
        $server->addPlugin(new SubscriptionsPlugin());
        $server->addPlugin(new SchedulePlugin());
        $server->addPlugin(new SyncPlugin());
        $server->addPlugin(new BrowserPlugin());

        $callback = function () use ($server, $request) {

            /* These two lines fix a weird bug
               where SabreDAV would give the correct answer to a propfind */
            $url = $server->httpRequest->getUrl();
            $server->httpRequest = new SabreRequest(
                $request->getMethod(), $url,
                $request->headers->all(), $request->getContent()
            );

            $server->exec();

            /* These two lines log the request and the response */
            $responseBody = $server->httpResponse->getBodyAsString();
            $this->logIt($request, $server->httpResponse, $responseBody);
        };

        return new StreamedResponse($callback);
    }

    /**
     * @param Request       $request
     * @param SabreResponse $response
     * @param string        $responseBody
     */
    private function logIt(Request $request, SabreResponse $response, $responseBody)
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

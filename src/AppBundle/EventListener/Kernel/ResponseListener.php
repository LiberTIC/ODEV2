<?php
/**
 * This file is part of the ODEv2InstallTest package.
 *
 * (c) 2015 Les Polypodes
 * Made in Nantes, France - http://lespolypodes.com
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 *
 * File created by ronan@lespolypodes.com (29/06/2015 - 17:41)
 */

namespace AppBundle\EventListener\Kernel;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * Class ResponseListener
 *
 * @package AppBundle\EventListener\Kernel
 */
class ResponseListener
{

    /**
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();
        $response->headers->set('X-Proudly-Crafted-By', 'twitter.com/libertic, lespolypodes.com & friends'); // It's nerdy, I know that.
        $response->headers->set('X-Open-Source', 'https://github.com/LiberTIC/ODEV2');
        $response->headers->set('Server', "It's a honeypot.");

        $event->setResponse($response);
    }

}

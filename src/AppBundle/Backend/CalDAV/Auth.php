<?php

namespace AppBundle\Backend\CalDAV;

use Sabre\DAV\Auth\Backend\AbstractDigest;

class Auth extends AbstractDigest
{
    protected $manager;

    public function __construct($manager)
    {
        $this->manager = $manager;
    }

    public function getDigestHash($realm, $username)
    {
        $searchResult = $this->manager->simpleQuery('app','users', ['usernameCanonical' => strtolower($username)]);

        if ($searchResult == null) {
            return;
        }

        return $searchResult[0]['_source']['passwordDigesta'];
    }
}

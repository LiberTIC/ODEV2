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
        $oldIndex = $this->manager->index;
        $this->manager->index = "app";
        $searchResult = $this->manager->simpleQuery('users', ['usernameCanonical' => strtolower($username)]);
        $this->manager->index = $oldIndex;

        if ($searchResult == null) {
            return;
        }

        return $searchResult[0]['_source']['passwordDigesta'];
    }
}

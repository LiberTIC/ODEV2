<?php

namespace AppBundle\Backend\CalDAV;

use Sabre\DAV\Auth\Backend\AbstractDigest;

use PommProject\Foundation\Where;

class Auth extends AbstractDigest
{
    protected $manager;

    public function __construct($manager)
    {
        $this->manager = $manager;
    }

    public function getDigestHash($realm, $username)
    {

        $where = Where::create('username_canonical = $*',[$username]);

        $users = $this->manager->findWhere('public','users',$where);

        if ($users->count() == 0) {
            return;
        }

        return $users->get(0)->password_digesta;
    }
}

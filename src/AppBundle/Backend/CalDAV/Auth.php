<?php

namespace AppBundle\Backend\CalDAV;

use Sabre\DAV\Auth\Backend\AbstractDigest;
use PommProject\Foundation\Where;
use AppBundle\Service\PommManager;

/**
 * Class Auth
 *
 * @package AppBundle\Backend\CalDAV
 */
class Auth extends AbstractDigest
{
    /**
     * @var PommManager
     */
    protected $manager;

    /**
     * @param PommManager $manager
     */
    public function __construct($manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function getDigestHash($realm, $username)
    {
        $where = Where::create('username_canonical = $*', [$username]);

        $users = $this->manager->findWhere('public', 'users', $where);

        if ($users->count() == 0) {
            return null;
        }

        return $users->get(0)->password_digesta;
    }
}

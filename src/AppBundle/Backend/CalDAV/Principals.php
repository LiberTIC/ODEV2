<?php

namespace AppBundle\Backend\CalDAV;

use Sabre\DAV;
use Sabre\Uri;
use Sabre\DAVACL\PrincipalBackend\AbstractBackend;
use Sabre\DAV\PropPatch;
use PommProject\Foundation\Where;
use AppBundle\Service\PommManager;

/**
 * Class Principals
 *
 * @package AppBundle\Backend\CalDAV
 */
class Principals extends AbstractBackend
{
    /**
     * @var PommManager
     */
    protected $manager;

    /**
     * @var array
     */
    protected $fieldMap = [

        '{DAV:}displayname' => [
            'dbField' => 'displayname',
        ],
        '{http://sabredav.org/ns}vcard-url' => [
            'dbField' => 'vcardurl',
        ],
        '{http://sabredav.org/ns}email-address' => [
            'dbField' => 'email',
        ],
    ];

    /**
     * @param PommManager $manager
     */
    public function __construct($manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param string $prefixPath
     *
     * @return array|null
     */
    public function getPrincipalsByPrefix($prefixPath)
    {
        $dbPrincipals = $this->manager->findAll('public', 'principal');

        if ($dbPrincipals->count() == 0) {
            return null;
        }

        $principals = [];

        foreach ($dbPrincipals as $dbPrincipal) {
            // Checking if the principal is in the prefix
            list($rowPrefix,$basename) = Uri\split($dbPrincipal->uri);
            if ($rowPrefix !== $prefixPath) {
                continue;
            }

            $principal = [
                'uri' => $dbPrincipal->uri,
            ];

            foreach ($this->fieldMap as $key => $value) {
                $principal[$key] = $dbPrincipal->$value['dbField'];
            }

            $principals[] = $principal;
        }

        return $principals;
    }

    /**
     * @param string $path
     *
     * @return array
     */
    public function getPrincipalByPath($path)
    {
        $where = Where::create('uri = $*', [$path]);

        $principals = $this->manager->findWhere('public', 'principal', $where);

        if ($principals->count() == 0) {
            return [];
        }

        $principal = $principals->get(0);

        $ret = [
            'id' => $principal->id,
            'uri' => $principal->uri,
        ];

        foreach ($this->fieldMap as $key => $value) {
            $ret[$key] = $principal->$value['dbField'];
        }

        return $ret;
    }

    /**
     * @param string    $path
     * @param PropPatch $propPatch
     *
     * @return null;
     */
    public function updatePrincipal($path, PropPatch $propPatch)
    {
        return null;
    }

    /**
     * @param string $prefixPath
     * @param array  $searchProperties
     * @param string $test
     *
     * @return array
     */
    public function searchPrincipals($prefixPath, array $searchProperties, $test = 'allof')
    {
        return array();
    }

    /**
     * @param string $principal
     *
     * @return array
     */
    public function getGroupMemberSet($principal)
    {
        return array();
    }

    /**
     * @param string $principal
     *
     * @return array
     */
    public function getGroupMembership($principal)
    {
        return array();
    }

    /**
     * @param string $principal
     * @param array  $members
     *
     * @return null
     */
    public function setGroupMemberSet($principal, array $members)
    {
        return null;
    }
}

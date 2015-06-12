<?php

namespace AppBundle\Backend\CalDAV;

use Sabre\DAV;
use Sabre\HTTP\URLUtil;
use Sabre\DAVACL\PrincipalBackend\AbstractBackend;
use PommProject\Foundation\Where;

class Principals extends AbstractBackend
{
    protected $manager;

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

    public function __construct($manager)
    {
        $this->manager = $manager;
    }

    public function getPrincipalsByPrefix($prefixPath)
    {
        $dbPrincipals = $this->manager->findAll('public', 'principal');

        if ($dbPrincipals->count() == 0) {
            return;
        }

        $principals = [];

        foreach ($dbPrincipals as $dbPrincipal) {
            // Checking if the principal is in the prefix
            list($rowPrefix) = URLUtil::splitPath($dbPrincipal->uri);
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

    public function updatePrincipal($path, \Sabre\DAV\PropPatch $PropPatch)
    {
        //echo "up";
    }

    public function searchPrincipals($prefixPath, array $searchProperties, $test = 'allof')
    {
        //echo "sp";
    }

    public function getGroupMemberSet($principal)
    {
        //echo "ggms";
        return [];
    }

    public function getGroupMembership($principal)
    {
        //echo "ggm";
        return [];
    }

    public function setGroupMemberSet($principal, array $members)
    {
        //echo "sgms";
    }
}

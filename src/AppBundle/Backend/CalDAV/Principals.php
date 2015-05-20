<?php

namespace AppBundle\Backend\CalDAV;

use Sabre\DAV;
use Sabre\HTTP\URLUtil;
use Sabre\DAVACL\PrincipalBackend\AbstractBackend;

class Principals extends AbstractBackend
{
    public $tableName = 'principals';

    public $groupMembersTableName = 'groupmembers';

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

    public function __construct($manager, $tableName = 'principals', $groupMembersTableName = 'groupmembers')
    {
        $this->manager = $manager;
        $this->tableName = $tableName;
        $this->groupMembersTableName = $groupMembersTableName;
    }

    public function getPrincipalsByPrefix($prefixPath)
    {
        $searchResult = $this->manager->complexQuery('caldav',$this->tableName, ['query' => ['filtered' => ['filter' => ['prefix' => ['uri' => $prefixPath]]]]]);

        $principals = [];

        if ($searchResult == null) {
            return;
        }

        foreach ($searchResult as $pr) {
            // Checking if the principal is in the prefix
            list($rowPrefix) = URLUtil::splitPath($pr['_source']['uri']);
            if ($rowPrefix !== $prefixPath) {
                continue;
            }

            $principal = [
                'uri' => $pr['_source']['uri'],
            ];

            foreach ($this->fieldMap as $key => $value) {
                $principal[$key] = $pr['_source'][$value['dbField']];
            }

            $principals[] = $principal;
        }

        return $principals;
    }

    public function getPrincipalByPath($path)
    {
        $searchResult = $this->manager->simpleQuery('caldav',$this->tableName, ['uri' => $path]);

        if ($searchResult == null) {
            return [];
        }

        $pr = $searchResult[0];

        $principal = [
            'id' => $pr['_source']['id'],
            'uri' => $pr['_source']['uri'],
        ];

        foreach ($this->fieldMap as $key => $value) {
            $principal[$key] = $pr['_source'][$value['dbField']];
        }

        return $principal;
    }

    public function updatePrincipal($path, \Sabre\DAV\PropPatch $propPatch)
    {

        /*$propPatch->handle(array_keys($this->fieldMap), function($properties) use ($path) {

            $query = "UPDATE " . $this->tableName . " SET ";
            $first = true;

            $values = [];

            foreach($properties as $key=>$value) {

                $dbField = $this->fieldMap[$key]['dbField'];

                if (!$first) {
                    $query.= ', ';
                }
                $first = false;
                $query.=$dbField . ' = :' . $dbField;
                $values[$dbField] = $value;

            }

            $query.=" WHERE uri = :uri";
            $values['uri'] = $path;

            $stmt = $this->pdo->prepare($query);
            $stmt->execute($values);

            return true;

        });*/
    }

    /**
     * This method is used to search for principals matching a set of
     * properties.
     *
     * This search is specifically used by RFC3744's principal-property-search
     * REPORT.
     *
     * The actual search should be a unicode-non-case-sensitive search. The
     * keys in searchProperties are the WebDAV property names, while the values
     * are the property values to search on.
     *
     * By default, if multiple properties are submitted to this method, the
     * various properties should be combined with 'AND'. If $test is set to
     * 'anyof', it should be combined using 'OR'.
     *
     * This method should simply return an array with full principal uri's.
     *
     * If somebody attempted to search on a property the backend does not
     * support, you should simply return 0 results.
     *
     * You can also just return 0 results if you choose to not support
     * searching at all, but keep in mind that this may stop certain features
     * from working.
     *
     * @param string $prefixPath
     * @param array  $searchProperties
     * @param string $test
     *
     * @return array
     */
    public function searchPrincipals($prefixPath, array $searchProperties, $test = 'allof')
    {

        /*$query = 'SELECT uri FROM ' . $this->tableName . ' WHERE 1=1 ';
        $values = [];
        foreach($searchProperties as $property => $value) {

            switch($property) {

                case '{DAV:}displayname' :
                    $query.=' AND displayname LIKE ?';
                    $values[] = '%' . $value . '%';
                    break;
                case '{http://sabredav.org/ns}email-address' :
                    $query.=' AND email LIKE ?';
                    $values[] = '%' . $value . '%';
                    break;
                default :
                    // Unsupported property
                    return [];

            }

        }
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($values);

        $principals = [];
        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {

            // Checking if the principal is in the prefix
            list($rowPrefix) = URLUtil::splitPath($row['uri']);
            if ($rowPrefix !== $prefixPath) continue;

            $principals[] = $row['uri'];

        }

        return $principals;*/

        return [];
    }

    /**
     * Returns the list of members for a group-principal.
     *
     * @param string $principal
     *
     * @return array
     */
    public function getGroupMemberSet($principal)
    {

        /*$principal = $this->getPrincipalByPath($principal);
        if (!$principal) throw new DAV\Exception('Principal not found');

        $stmt = $this->pdo->prepare('SELECT principals.uri as uri FROM '.$this->groupMembersTableName.' AS groupmembers LEFT JOIN '.$this->tableName.' AS principals ON groupmembers.member_id = principals.id WHERE groupmembers.principal_id = ?');
        $stmt->execute([$principal['id']]);

        $result = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $result[] = $row['uri'];
        }
        return $result;*/

        return [];
    }

    /**
     * Returns the list of groups a principal is a member of.
     *
     * @param string $principal
     *
     * @return array
     */
    public function getGroupMembership($principal)
    {

        /*$principal = $this->getPrincipalByPath($principal);
        if (!$principal) throw new DAV\Exception('Principal not found');

        $stmt = $this->pdo->prepare('SELECT principals.uri as uri FROM '.$this->groupMembersTableName.' AS groupmembers LEFT JOIN '.$this->tableName.' AS principals ON groupmembers.principal_id = principals.id WHERE groupmembers.member_id = ?');
        $stmt->execute([$principal['id']]);

        $result = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $result[] = $row['uri'];
        }
        return $result;*/

        return [];
    }

    /**
     * Updates the list of group members for a group principal.
     *
     * The principals should be passed as a list of uri's.
     *
     * @param string $principal
     * @param array  $members
     */
    public function setGroupMemberSet($principal, array $members)
    {

        // Grabbing the list of principal id's.
        /*$stmt = $this->pdo->prepare('SELECT id, uri FROM '.$this->tableName.' WHERE uri IN (? ' . str_repeat(', ? ', count($members)) . ');');
        $stmt->execute(array_merge([$principal], $members));

        $memberIds = [];
        $principalId = null;

        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if ($row['uri'] == $principal) {
                $principalId = $row['id'];
            } else {
                $memberIds[] = $row['id'];
            }
        }
        if (!$principalId) throw new DAV\Exception('Principal not found');

        // Wiping out old members
        $stmt = $this->pdo->prepare('DELETE FROM '.$this->groupMembersTableName.' WHERE principal_id = ?;');
        $stmt->execute([$principalId]);

        foreach($memberIds as $memberId) {

            $stmt = $this->pdo->prepare('INSERT INTO '.$this->groupMembersTableName.' (principal_id, member_id) VALUES (?, ?);');
            $stmt->execute([$principalId, $memberId]);

        }*/

        return [];
    }
}

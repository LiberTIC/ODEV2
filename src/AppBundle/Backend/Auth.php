<?php

namespace AppBundle\Backend;

use Sabre\DAV\Auth\Backend\AbstractDigest;

class Auth extends AbstractDigest
{
    protected $client;

    public function __construct($client)
    {
        $this->client = $client;

        $this->manager = new ESManager($client);
    }

    public function getDigestHash($realm, $username)
    {
        $searchResult = $this->manager->simpleQuery('users', ['username' => $username]);

        if ($searchResult == null) {
            return;
        }

        return $searchResult[0]['_source']['digesta1'];

        /*$stmt = $this->pdo->prepare('SELECT digesta1 FROM '.$this->tableName.' WHERE username = ?');
        $stmt->execute([$username]);
        return $stmt->fetchColumn() ?: null;*/
    }
}

<?php

namespace AppBundle\Entity;

use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

class User extends BaseUser implements JsonSerializable
{

    protected $id;

    private $passwordDigesta;

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }

    public function setId($id) {
    	$this->id = $id;
    }

    public function setSalt($salt) {
    	$this->salt = $salt;
    }

    public function getPasswordDigesta() {
        return $this->passwordDigesta;
    }

    public function setPasswordDigesta($passwordDigesta) {
        $this->passwordDigesta = $passwordDigesta;
    }

    public function jsonSerialize() {
    	return [
    		"id" => $this->id,
    		"username" => $this->username,
    		"usernameCanonical" => $this->usernameCanonical,
    		"email" => $this->email,
    		"emailCanonical" => $this->emailCanonical,
    		"enabled" => $this->enabled,
    		"salt" => $this->salt,
    		"password" => $this->password,
            "passwordDigesta" => $this->passwordDigesta,
    		"lastLogin" => $this->lastLogin,
    		"locked" => $this->locked,
    		"expired" => $this->expired,
    		"expires_at" => $this->expiresAt,
    		"confirmationToken" => $this->confirmationToken,
    		"passwordRequestedAt" => $this->passwordRequestedAt,
    		"roles" => $this->roles,
    		"credentialsExpired" => $this->credentialsExpired,
    		"credentialsExpireAt" => $this->credentialsExpireAt
    	];
    }
}
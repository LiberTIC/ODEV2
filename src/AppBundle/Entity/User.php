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

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setSalt($salt)
    {
        $this->salt = $salt;
    }

    public function getPasswordDigesta()
    {
        return $this->passwordDigesta;
    }

    public function setPasswordDigesta($passwordDigesta)
    {
        $this->passwordDigesta = $passwordDigesta;
    }

    public function __set($name,$value) {
        $name = preg_replace('/_(.?)/e',"strtoupper('$1')",$name);
        $this->$name = $value;
    }

    public function jsonSerialize()
    {
        return [
            "username" => $this->username,
            "username_canonical" => $this->usernameCanonical,
            "email" => $this->email,
            "email_canonical" => $this->emailCanonical,
            "enabled" => $this->enabled,
            "salt" => $this->salt,
            "password" => $this->password,
            "password_digesta" => $this->passwordDigesta,
            "last_login" => $this->lastLogin,
            "locked" => $this->locked,
            "expired" => $this->expired,
            "expires_at" => $this->expiresAt,
            "confirmation_token" => $this->confirmationToken,
            "password_requested_at" => $this->passwordRequestedAt,
            "roles" => $this->roles,
            "credentials_expired" => $this->credentialsExpired,
            "credentials_expire_at" => $this->credentialsExpireAt
        ];
    }
}

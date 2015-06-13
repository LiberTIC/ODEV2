<?php

namespace AppBundle\Entity;

use FOS\UserBundle\Entity\User as BaseUser;
use JsonSerializable;

/**
 * Class User
 *
 * @package AppBundle\Entity
 */
class User extends BaseUser implements JsonSerializable
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    private $passwordDigesta;

    /**
     * .ctor()
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param string $salt
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
    }

    /**
     * @return string
     */
    public function getPasswordDigesta()
    {
        return $this->passwordDigesta;
    }

    /**
     * @param string $passwordDigesta
     */
    public function setPasswordDigesta($passwordDigesta)
    {
        $this->passwordDigesta = $passwordDigesta;
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function __set($name, $value)
    {
        $name = preg_replace('/_(.?)/e', "strtoupper('$1')", $name);
        $this->$name = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [
            'username' => $this->username,
            'username_canonical' => $this->usernameCanonical,
            'email' => $this->email,
            'email_canonical' => $this->emailCanonical,
            'enabled' => $this->enabled,
            'salt' => $this->salt,
            'password' => $this->password,
            'password_digesta' => $this->passwordDigesta,
            'last_login' => $this->lastLogin,
            'locked' => $this->locked,
            'expired' => $this->expired,
            'expires_at' => $this->expiresAt,
            'confirmation_token' => $this->confirmationToken,
            'password_requested_at' => $this->passwordRequestedAt,
            'roles' => $this->roles,
            'credentials_expired' => $this->credentialsExpired,
            'credentials_expire_at' => $this->credentialsExpireAt,
        ];
    }
}

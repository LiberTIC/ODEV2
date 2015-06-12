<?php

namespace AppBundle\Backend\Users;

use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Model\UserInterface;

use PommProject\Foundation\Where;

class UserManager implements UserManagerInterface
{
    private $manager;

    private $encoderFactory;

    public function __construct($manager)
    {
        $this->manager = $manager;
    }

    public function createUser()
    {
        $class = $this->getClass();
        return new $class;
    }

    public function createFromDatabase($dbUser)
    {
        $user = $this->createUser();

        $dbUser = $dbUser->extract();

        foreach($dbUser as $name => $value) {
            $user->$name = $value;
        }

        return $user;
    }

    public function deleteUser(UserInterface $user)
    {
        $this->manager->query('DELETE FROM users WHERE id = '.$user->getId());
    }

    public function findUserBy(array $criteria)
    {

        $key = array_keys($criteria)[0];
        $where = Where::create($key." = $*",[$criteria[$key]]);

        $users = $this->manager->findWhere('public','users',$where);

        if ($users->count() == 0) {
            return null;
        }

        $ret = $this->createFromDatabase($users->get(0));
        return $ret;
        
    }

    public function findUserByUsername($username)
    {
        return $this->findUserBy(['username_canonical' => $username]);
    }

    public function findUserByEmail($email)
    {
        return $this->findUserBy(['email_canonical' => $email]);
    }

    public function findUserByUsernameOrEmail($usernameOrEmail)
    {
        if (filter_var($usernameOrEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->findUserByEmail($usernameOrEmail);
        }

        return $this->findUserByUsername($usernameOrEmail);
    }

    public function findUserByConfirmationToken($token)
    {
        return $this->findUserByUsername(['confirmation_token' => $token]);
    }

    public function findUsers()
    {

        $users = $this->manager->findAll('public','users');

        $ret = [];
        foreach($users as $user) {
            $ret[] = $this->createFromDatabase($user);
        }

        return $ret;
    }

    public function getClass()
    {
        return 'AppBundle\Entity\User';
    }

    public function reloadUser(UserInterface $user)
    {
        return $this->findUserBy(['id' => $user->getId()]);
    }

    public function updateUser(UserInterface $user)
    {

        $this->updatePassword($user);

        if ($user->getId() == null) {

            $this->createPrincipals($user);

            $ret = $this->manager->insertOne('public','users',$user->jsonSerialize());

            $user->setId($ret->id);

        } else {

            $where = Where::create('id = $*',[$user->getId()]);
            $dbUser = $this->manager->findWhere('public','users',$where)->get(0);

            $data = $user->jsonSerialize();
            foreach($data as $name => $value) {
                $dbUser->$name = $value;
            }

            $this->manager->updateOne('public','users',$dbUser,array_keys($data));
            
        }
    }

    public function updateCanonicalFields(UserInterface $user)
    {
        $user->setUsernameCanonical(strtolower($user->getUsername()));
        $user->setEmailCanonical(strtolower($user->getEmail()));
    }

    public function updatePassword(UserInterface $user)
    {
        if (0 !== strlen($password = $user->getPlainPassword())) {
            $passwordDigesta = md5($user->getUsernameCanonical().":SabreDAV:".$password);

            $salt = $user->getSalt();
            $salted = $password.'{'.$salt.'}';
            $digest = hash('sha512', $salted, true);

            for ($i=1; $i<5000; $i++) {
                $digest = hash('sha512', $digest.$salted, true);
            }

            $encodedPassword = base64_encode($digest);

            $user->setPassword($encodedPassword);
            $user->setPasswordDigesta($passwordDigesta);
            $user->eraseCredentials();
        }
    }

    public function createPrincipals($user)
    {

        $username = $user->getUsername();
        $usernameCanonical = $user->getUsernameCanonical();
        $email = $user->getEmail();

        $principal = ['uri' => 'principals/'.$usernameCanonical, 'email' => $email, 'displayname' => $username, 'vcardurl' => null];
        $this->manager->insertOne('public','principal',$principal);

        $principal['uri'] = 'principals/'.$usernameCanonical.'/calendar-proxy-read';
        $this->manager->insertOne('public','principal',$principal);

        $principal['uri'] = 'principals/'.$usernameCanonical.'/calendar-proxy-write';
        $this->manager->insertOne('public','principal',$principal);

    }
}

<?php

namespace AppBundle\Backend\Users;

use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Model\UserInterface;
use Elasticsearch\Client;
use AppBundle\Backend\ESManager;

class UserManager implements UserManagerInterface
{
    private $esmanager;

    private $encoderFactory;

    public function __construct($manager)
    {
        $this->esmanager = $manager;
    }

    public function createUser()
    {
        $class = $this->getClass();
        return new $class;
    }

    public function deleteUser(UserInterface $user)
    {
        $this->esmanager->simpleDelete('app', 'users', $user->getId());
    }

    public function findUserBy(array $criteria)
    {

        if (isset($criteria['id'])) {
            $u = $this->esmanager->simpleGet('app','users',$criteria['id']);

            if ($u == null) {
                return null;
            }

        } else {
            $searchResult = $this->esmanager->simpleQuery('app', 'users', $criteria);

            if ($searchResult == null) {
                return null;
            }

            $u = $searchResult[0];
        }

        return $this->loadUserFromArray($u);
    }

    public function findUserByUsername($username)
    {
        return $this->findUserBy(['usernameCanonical' => $username]);
    }

    public function findUserByEmail($email)
    {
        return $this->findUserBy(['emailCanonical' => $email]);
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
        return $this->findUserByUsername(['confirmationToken' => $token]);
    }

    public function findUsers()
    {
        $searchResult = $this->esmanager->simpleSearch('app', 'users');
        $t = null;
        echo $t->truc();
        $users = [];
        foreach ($searchResult as $u) {
            $users[] = $this->loadUserFromArray($u);
        }

        return $users;
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
        }
        
        $this->esmanager->simpleIndex('app', 'users', null, $user->jsonSerialize());
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





    public function loadUserFromArray($u)
    {
        $user = $this->createUser();
        $user->setId($u['_id']);

        $u = $u['_source'];

        $user->setUsername($u['username']);
        $user->setUsernameCanonical($u['usernameCanonical']);
        $user->setEmail($u['email']);
        $user->setEmailCanonical($u['emailCanonical']);
        $user->setEnabled($u['enabled']);
        $user->setSalt($u['salt']);
        $user->setPassword($u['password']);
        if ($u['lastLogin'] != null) {
            $user->setLastLogin(\DateTime::createFromFormat("Y-m-d H:i:s.u", $u['lastLogin']['date']));
        }
        $user->setLocked($u['locked']);
        $user->setExpired($u['locked']);
        $user->setConfirmationToken($u['confirmationToken']);
        foreach ($u['roles'] as $role) {
            $user->addRole($role);
        }

        return $user;
    }

    public function createPrincipals($user)
    {
        $username = $user->getUsername();
        $usernameCanonical = $user->getUsernameCanonical();
        $email = $user->getEmail();

        $principal = ['uri' => 'principals/'.$usernameCanonical, 'email' => $email, 'displayname' => $username, 'vcardurl' => null];
        $this->esmanager->simpleIndex('caldav', 'principals', null, $principal);

        $principal['uri'] = 'principals/'.$usernameCanonical.'/calendar-proxy-read';
        $this->esmanager->simpleIndex('caldav', 'principals', null, $principal);

        $principal['uri'] = 'principals/'.$usernameCanonical.'/calendar-proxy-write';
        $this->esmanager->simpleIndex('caldav', 'principals', null, $principal);
    }
}

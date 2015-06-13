<?php

namespace AppBundle\Backend\Users;

use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Model\UserInterface;
use PommProject\ModelManager\Model\FlexibleEntity\FlexibleEntityInterface;
use PommProject\Foundation\Where;
use AppBundle\Service\PommManager;
use AppBundle\Entity\User;

/**
 * Class UserManager
 *
 * @package AppBundle\Backend\Users
 */
class UserManager implements UserManagerInterface
{
    /**
     * @var PommManager
     */
    private $manager;

    /**
     * @param PommManager $manager
     */
    public function __construct(PommManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param UserInterface $user
     */
    public function createPrincipals(UserInterface $user)
    {
        $username = $user->getUsername();
        $usernameCanonical = $user->getUsernameCanonical();
        $email = $user->getEmail();

        $principal = ['uri' => 'principals/'.$usernameCanonical, 'email' => $email, 'displayname' => $username, 'vcardurl' => null];
        $this->manager->insertOne('public', 'principal', $principal);

        $principal['uri'] = 'principals/'.$usernameCanonical.'/calendar-proxy-read';
        $this->manager->insertOne('public', 'principal', $principal);

        $principal['uri'] = 'principals/'.$usernameCanonical.'/calendar-proxy-write';
        $this->manager->insertOne('public', 'principal', $principal);
    }

    /**
     * @param FlexibleEntityInterface $dbUser
     *
     * @return User
     */
    public function createFromDatabase(FlexibleEntityInterface $dbUser)
    {
        $user = $this->createUser();

        $dbUser = $dbUser->extract();

        foreach ($dbUser as $name => $value) {
            $user->$name = $value;
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteUser(UserInterface $user)
    {
        $this->manager->query('DELETE FROM users WHERE id = '.$user->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function findUserByUsernameOrEmail($usernameOrEmail)
    {
        if (filter_var($usernameOrEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->findUserByEmail($usernameOrEmail);
        }

        return $this->findUserByUsername($usernameOrEmail);
    }

    /**
     * {@inheritdoc}
     */
    public function findUserByEmail($email)
    {
        return $this->findUserBy(['email_canonical' => $email]);
    }

    /**
     * {@inheritdoc}
     */
    public function findUserBy(array $criteria)
    {
        $key = array_keys($criteria)[0];
        $where = Where::create($key.' = $*', [$criteria[$key]]);

        $users = $this->manager->findWhere('public', 'users', $where);

        if ($users->count() == 0) {
            return;
        }

        $ret = $this->createFromDatabase($users->get(0));

        return $ret;
    }

    /**
     * {@inheritdoc}
     */
    public function createUser()
    {
        $class = $this->getClass();

        return new $class();
    }

    /**
     * {@inheritdoc}
     */
    public function getClass()
    {
        return 'AppBundle\Entity\User';
    }

    /**
     * {@inheritdoc}
     */
    public function findUserByUsername($username)
    {
        return $this->findUserBy(['username_canonical' => $username]);
    }

    /**
     * {@inheritdoc}
     */
    public function findUserByConfirmationToken($token)
    {
        return $this->findUserByUsername(['confirmation_token' => $token]);
    }

    /**
     * {@inheritdoc}
     */
    public function findUsers()
    {
        $users = $this->manager->findAll('public', 'users');

        $ret = [];
        foreach ($users as $user) {
            $ret[] = $this->createFromDatabase($user);
        }

        return $ret;
    }

    /**
     * {@inheritdoc}
     */
    public function reloadUser(UserInterface $user)
    {
        return $this->findUserBy(['id' => $user->getId()]);
    }

    /**
     * {@inheritdoc}
     */
    public function updateUser(UserInterface $user)
    {
        $this->updatePassword($user);

        if ($user->getId() == null) {
            $this->createPrincipals($user);

            $ret = $this->manager->insertOne('public', 'users', $user->jsonSerialize());

            $user->setId($ret->id);
        } else {
            $where = Where::create('id = $*', [$user->getId()]);
            $dbUser = $this->manager->findWhere('public', 'users', $where)->get(0);

            $data = $user->jsonSerialize();
            foreach ($data as $name => $value) {
                $dbUser->$name = $value;
            }

            $this->manager->updateOne('public', 'users', $dbUser, array_keys($data));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updatePassword(UserInterface $user)
    {
        if (0 !== strlen($password = $user->getPlainPassword())) {
            $passwordDigesta = md5($user->getUsernameCanonical().':SabreDAV:'.$password);

            $salt = $user->getSalt();
            $salted = $password.'{'.$salt.'}';
            $digest = hash('sha512', $salted, true);

            for ($i = 1; $i < 5000; $i++) {
                $digest = hash('sha512', $digest.$salted, true);
            }

            $encodedPassword = base64_encode($digest);

            $user->setPassword($encodedPassword);
            $user->setPasswordDigesta($passwordDigesta);
            $user->eraseCredentials();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateCanonicalFields(UserInterface $user)
    {
        $user->setUsernameCanonical(strtolower($user->getUsername()));
        $user->setEmailCanonical(strtolower($user->getEmail()));
    }
}

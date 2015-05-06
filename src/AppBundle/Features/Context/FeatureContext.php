<?php

namespace AppBundle\Features\Context;

use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\MinkExtension\Context\MinkContext;
use Symfony\Component\HttpKernel\KernelInterface;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends MinkContext implements KernelAwareContext
{
    private $kernel;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var
     */
    private $driver;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct(Session $session, $simpleArg)
    {
    }

    /**
     * Sets HttpKernel instance.
     * This method will be automatically called by Symfony2Extension ContextInitializer.
     *
     * @param KernelInterface $kernel
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @Given /^que je suis à "([^"]*)"$/
     */
    public function queJeSuisA($arg1)
    {
        $this->jeVaisA($arg1);
    }

    /**
     * @Given /^que je vais à "([^"]*)"$/
     */
    public function queJeVaisA($arg1)
    {
        $this->jeVaisA($arg1);
    }

    /**
     * @Given /^je vais à "([^"]*)"$/
     */
    public function jeVaisA($arg1)
    {
        $this->session = $this->getCustomSession();
        $this->session->visit($arg1);
    }

    /**
     * @Given /^que je saisie des identifiants de connexion$/
     */
    public function queJeSaisieDesIdentifiantsDeConnexion(TableNode $table)
    {
        $hash = $table->getHash();
        $this->session = $this->getSession();
        $this->session->setRequestHeader('Accept-Language', 'fr');
        $page = $this->session->getPage();

        $page->fillField('username', $hash[0]['login']);
        $page->fillField('password', $hash[0]['password']);
    }

     /**
     * @Given /^que je saisis une tâche$/
     */
    public function queJeSaisisUneTache(TableNode $table)
    {
        $hash = $table->getHash();
        $this->session = $this->getSession();
        $this->session->setRequestHeader('Accept-Language', 'fr');
        $page = $this->session->getPage();

        $page->fillField('task_task', $hash[0]['task_task']);
        $page->fillField('task_dueDate', $hash[0]['task_dueDate']);
        $page->fillField('task_category_name', $hash[0]['task_category_name']);
    }

    /**
     * @Given /^que je clique sur le bouton "([^"]*)"$/
     */
    public function queJeCliqueSurLeBouton($arg1)
    {
        $this->session = $this->getCustomSession();
        $page = $this->session->getPage();
        $page->find('css', sprintf('#%s', $arg1))->click();
    }

    /**
     * @Given /^que je clique sur le lien "([^"]*)"$/
     */
    public function queJeCliqueSurLeLien($arg1)
    {
        $this->clickLink($arg1);
    }

    /**
     * @Given /^que je valide le formulaire$/
     */
    public function queJeValideLeFormulaire()
    {
        $this->session = $this->getCustomSession();
        $page = $this->session->getPage();
        $page->find('css', '[type=submit]')->submit();
    }

    protected function getCustomSession()
    {
        //return $this->getSession();
        //$this->session = $this->getSession();
        /*
        if (!$driver instanceof BrowserKitDriver) {
            throw new UnsupportedDriverActionException('This step is only supported by the BrowserKitDriver', $driver);
        }
        $client = $driver->getC;
        $session = $client->getContainer()->get('session');
        */
       // $this->session->get('Accept-Language', 'fr');

        return  $this->getSession();
    }
}

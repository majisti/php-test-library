<?php

namespace Majisti\Testing\Database;

use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Will help with fixtures loading and caching, as well as manipulating the test database while doing tests.
 *
 * TODO: Database fixtures caching. For now, I did not have enough time to implement this and therefore fixtures
 * are reloaded each time, which will make tests run slow in the longer term.
 *
 * @author Steven Rosato
 */
class DatabaseHelper
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @var array
     */
    private $aliceFixturesList;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $this->container = $kernel->getContainer();
    }

    /**
     * @param TestCase $test
     */
    public function beforeTest(TestCase $test)
    {
        $this->initFixturesList();
        $this->loadFixtures();
    }

    /**
     * @param TestCase $test
     */
    public function afterTest(TestCase $test)
    {
    }

    private function initFixturesList()
    {
        $fixturesFinder = $this->getContainer()->get('hautelook_alice.doctrine.orm.fixtures_finder');
        $this->aliceFixturesList = $fixturesFinder->getFixtures(
            $this->getKernel(), $this->getKernel()->getBundles(),
            $this->getKernel()->getEnvironment()
        );
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getContainer()->get('doctrine.orm.entity_manager');
    }

    public function loadFixtures()
    {
        //FIXME: this will not take from cache!
        if (count($this->aliceFixturesList)) {
            $container = $this->getContainer();

            $loaderGenerator = $container->get('hautelook_alice.doctrine.orm.loader_generator');
            $loader = $container->get('hautelook_alice.fixtures.loader');
            $fixturesLoader = $container->get('hautelook_alice.alice.fixtures.loader');

            $fixturesExecutor = $container->get('hautelook_alice.doctrine.executor.fixtures_executor');
            $fixturesExecutor->execute(
                $this->getEntityManager(),
                $loaderGenerator->generate($loader, $fixturesLoader, $this->getKernel()->getBundles(), $this->getKernel()->getEnvironment()),
                $this->aliceFixturesList,
                false, //FIXME: should be true
                function () {},
                false
            );
        }
    }

    /**
     * @return ContainerInterface
     */
    private function getContainer()
    {
        return $this->getKernel()->getContainer();
    }

    private function getKernel()
    {
        return $this->kernel;
    }
}

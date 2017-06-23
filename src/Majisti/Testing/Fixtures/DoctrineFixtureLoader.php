<?php
/**
 * @copyright 2012 Instaclick Inc.
 */

namespace Majisti\Testing\Fixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\ProxyReferenceRepository;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader as SymfonyFixtureLoader;
use Symfony\Bundle\FrameworkBundle\Client;

/**
 * Data Fixture Loader
 * Implementation inspired by LiipFunctionalTestBundle.
 *
 * @author Juti Noppornpitak <jnopporn@shiroyuki.com>
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 * @author John Cartwright <jcartdev@gmail.com>
 */
class DoctrineFixtureLoader implements FixturesLoader
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var int
     */
    private $purgeMode;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * Constructor.
     *
     * @param Client $client
     * @param EntityManager $entityManager
     * @param int $purgeMode
     */
    public function __construct(Client $client, EntityManager $entityManager, $purgeMode = ORMPurger::PURGE_MODE_DELETE)
    {
        $this->client = $client;
        $this->purgeMode = $purgeMode;
        $this->entityManager = $entityManager;
    }

    public function getFixturesList()
    {
        $loader = new Loader();

        $fixturesList = [];
        foreach ($this->getDataFixturesPaths() as $path) {
            if (is_dir($path)) {
                $fixturesList = array_merge($fixturesList, $loader->loadFromDirectory($path));
            }
        }

        return $fixturesList;
    }

    /**
     * @return array
     */
    protected function getDataFixturesPaths()
    {
        $paths = array();
        foreach ($this->client->getKernel()->getBundles() as $bundle) {
            $paths[] = $bundle->getPath().'/DataFixtures/ORM';
        }

        return $paths;
    }

    /**
     * Set the database to the provided fixtures.
     *
     * Refreshes the database and loads fixtures using the specified classes.
     * List of classes is an argument accepting a list of fully qualified class names.
     * These classes must implement Doctrine\Common\DataFixtures\FixtureInterface to be loaded
     * effectively by DataFixtures Loader::addFixture
     *
     * When using SQLite driver, this method will work using 2 levels of cache.
     * - The first cache level will copy the loaded schema, so it can be restored automatically
     * without the overhead of creating the schema for every test case.
     * - The second cache level will copy the schema and fixtures loaded, restoring automatically
     * in the case you are reusing the same fixtures are loaded again.
     *
     * Depends on the doctrine data-fixtures library being available in the class path.
     *
     * @param bool $append
     *
     * @return ORMExecutor
     *
     * @internal param array $fixturesList
     * @internal param string $managerName Manager Name
     * @internal param array $classList Class List
     */
    public function load($append = false)
    {
        $executor = $this->prepareExecutor($this->entityManager);
        $this->loadFixturesList($executor, $this->getFixturesList(), $append);

        return $executor;
    }

    /**
     * @param EntityManager $entityManager
     *
     * @return ORMExecutor
     */
    private function prepareExecutor(EntityManager $entityManager)
    {
        $purger = new ORMPurger($entityManager);

        $purger->setPurgeMode($this->purgeMode);

        $executor = new ORMExecutor($entityManager, $purger);
        $repository = new ProxyReferenceRepository($entityManager);

        $executor->setReferenceRepository($repository);

        return $executor;
    }

    private function loadFixturesList(ORMExecutor $executor, array $classList, $append)
    {
        $loader = $this->getLoader($classList);
        $fixtureList = $loader->getFixtures();

        $executor->execute($fixtureList, $append);
    }

    /**
     * @param array $classList
     *
     * @return SymfonyFixtureLoader
     */
    private function getLoader(array $classList)
    {
        $container = $this->client->getContainer();
        $loader = new SymfonyFixtureLoader($container);

        foreach ($classList as $className) {
            $this->loadFixtureClass($loader, $className);
        }

        return $loader;
    }

    private function loadFixtureClass(SymfonyFixtureLoader $loader, $className)
    {
        $fixture = new $className();

        if ($loader->hasFixture($fixture)) {
            return;
        }

        $loader->addFixture($fixture);

        if (!$fixture instanceof DependentFixtureInterface) {
            return;
        }

        foreach ($fixture->getDependencies() as $dependency) {
            $this->loadFixtureClass($loader, $dependency);
        }
    }
}

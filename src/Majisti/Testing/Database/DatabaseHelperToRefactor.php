<?php

namespace Tests\Codeception\Module;

use Codeception\Configuration;
use Codeception\Exception\Configuration as ConfigurationException;
use Codeception\Module\Symfony2 as Symfony2Module;
use Codeception\TestCase;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Driver\PDOSqlite\Driver as SqliteDriver;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use IC\Bundle\Base\TestBundle\Test\Loader\DoctrineFixtureLoader;
use IC\Bundle\Base\TestBundle\Test\Loader\SchemaLoader;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Kernel;
use Tests\Codeception\Util\LoadSchemaOnlyInterface;

/**
 * The symfony doctrine context will autoload cached|fresh fixtures.
 *
 * This class really needs to go in a library and be properly refactored and tested.
 *
 * @author Steven Rosato
 */
class DatabaseHelperToRefactor
{
    /**
     * @var ReferenceRepository
     */
    private $referenceRepository;

    /**
     * @var array
     */
    private $fixturesList = array();

    /**
     * @var string
     */
    private $databaseDir;

    /**
     * @param array $settings
     *
     * @throws ConfigurationException
     */
    public function _beforeSuite($settings = array())
    {
        $this->initFixturesList();
        $this->initDatabaseDirectory();
    }

    private function initDatabaseDirectory()
    {
        $this->databaseDir = Configuration::logDir().'/databases';
        @mkdir($this->databaseDir, 0775, true);
    }

    private function initFixturesList()
    {
        $loader = new Loader();

        foreach ($this->getDataFixturesPaths() as $path) {
            if (is_dir($path)) {
                $this->fixturesList = array_merge($this->fixturesList, $loader->loadFromDirectory($path));
            }
        }
    }

    /**
     * @param TestCase $test
     */
    public function _before(TestCase $test)
    {
        if ($this->shouldLoadSchemaOnly($test)) {
            $this->loadDatabaseSchema();
        } else {
            $this->loadFixtures();
        }
    }

    /**
     * @param TestCase $test
     */
    public function _after(TestCase $test)
    {
        if ($test->hasFailed()) {
            $this->copyCurrentDatabase(sprintf('%s.fail', $this->canonicalizeDatabaseFileName($test->getFileName())), $this->databaseDir);
        }
    }

    /**
     * @param TestCase $test
     *
     * @return bool
     */
    private function shouldLoadSchemaOnly(TestCase $test)
    {
        return ($test instanceof TestCase\Cest && in_array('loadDatabaseSchemaOnly', $test->getScenario()->getGroups())) ||
            $test instanceof LoadSchemaOnlyInterface ||
            count($this->fixturesList) <= 0;
    }

    /**
     * @param $fileName
     *
     * @return string
     */
    private function canonicalizeDatabaseFileName($fileName)
    {
        return basename(str_replace(':', '_', $fileName));
    }

    /**
     * @return bool
     */
    private function isCachedDatabasePopulated()
    {
        return file_exists($this->getCachedDatabasePath());
    }

    /**
     * @return bool
     */
    private function isDatabaseSchemaCached()
    {
        return file_exists($this->getCachedDatabaseSchemaPath());
    }

    /**
     * @return string
     */
    private function getCachedDatabaseSchemaPath()
    {
        $metadataList = $this->getManager()->getMetadataFactory()->getAllMetadata();

        return sprintf('%s/test_%s.db', $this->getCacheDirectory(), md5(serialize($metadataList)));
    }

    /**
     * Copies the current database to the specified directory, if no directory is given, it will use
     * the cache directory. If there is a transaction active, it will be committed. Works only with
     * SqliteDriver.
     *
     * @param $newName
     * @param string $newDir
     * @param bool $commitDatabase
     *
     * @throws ConnectionException
     */
    public function copyCurrentDatabase($newName, $newDir = null, $commitDatabase = true)
    {
        $manager = $this->getManager();

        /* @var $manager EntityManager */
        if ($manager instanceof EntityManager) {
            $connection = $manager->getConnection();
            $driver = $connection->getDriver();

            //clean code needed...
            if ($driver instanceof SqliteDriver) {
                if ($connection->isTransactionActive()) {
                    if ($connection->isRollbackOnly()) {
                        return;
                    }

                    if ($commitDatabase) {
                        $connection->commit();
                    }
                }

                $currentDatabasePath = $this->getDatabasePath();
                if (file_exists($currentDatabasePath)) {
                    copy($currentDatabasePath, sprintf('%s/%s.sqlite', $newDir === null ? $this->getCacheDirectory() : $newDir, $newName));
                }
            }
        }
    }

    /**
     * @return string
     */
    private function getCacheDirectory()
    {
        return $this->getContainer()->getParameter('kernel.cache_dir');
    }

    /**
     * @return string
     */
    private function getDatabasePath()
    {
        return $this->getManager()->getConnection()->getParams()['path'];
    }

    /**
     * @return string
     */
    private function getCachedDatabasePath()
    {
        return sprintf('%s/test_populated_%s.db', $this->getCacheDirectory(), md5(serialize($this->fixturesList)));
    }

    /**
     * Snapshots the current database without altering the current cached data.
     *
     * @param string $name
     */
    public function snapshotDatabase($name = null)
    {
        if (null === $name) {
            $name = date('Y-m-d_H-i');
        }

        $snapshotName = sprintf('%s.snapshot', $name);
        $tempDatabaseBackup = $this->getCacheDirectory().'/current-running-database.sqlite';

        if ($this->isCachedDatabasePopulated()) {
            copy($this->getCachedDatabasePath(), $tempDatabaseBackup);
        }

        $this->copyCurrentDatabase($snapshotName, $this->databaseDir);

        if ($this->isCachedDatabasePopulated()) {
            copy($tempDatabaseBackup, $this->getCachedDatabasePath());
            unlink($tempDatabaseBackup);
        }
    }

    /**
     * @param bool $cancelActiveTransaction
     */
    public function emptyTheDatabase($cancelActiveTransaction = false)
    {
        if ($cancelActiveTransaction) {
            $this->rollbackTransaction();
        }

        $purger = new ORMPurger($this->getManager());
        $purger->purge();

        if ($cancelActiveTransaction) {
            $this->beginTransaction();
        }
    }

    private function rollbackTransaction()
    {
        $manager = $this->getManager();

        /* @var $manager EntityManager */
        if ($manager instanceof EntityManager) {
            $connection = $manager->getConnection();
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }
        }
    }

    private function beginTransaction()
    {
        $manager = $this->getManager();

        if ($manager instanceof EntityManager) {
            $connection = $manager->getConnection();
            $connection->beginTransaction();
        }
    }

    /**
     * @param bool $cancelActiveTransaction
     */
    public function repopulateTheDatabase($cancelActiveTransaction = false)
    {
        if ($cancelActiveTransaction) {
            $this->rollbackTransaction();
        }

        $this->loadFixtures();

        if ($cancelActiveTransaction) {
            $this->beginTransaction();
        }
    }

    /**
     * @return EntityManager
     */
    protected function getManager()
    {
        $managerRegistry = $this->getContainer()->get('doctrine');

        return $managerRegistry->getManager();
    }

    /**
     * @param bool $cancelActiveTransaction
     */
    public function loadDatabaseSchema($cancelActiveTransaction = false)
    {
        if ($cancelActiveTransaction) {
            $this->rollbackTransaction();
        }

        $loader = new SchemaLoader($this->getManager());
        $loader->setCacheDirectory($this->getCacheDirectory());
        $loader->load(ORMPurger::PURGE_MODE_DELETE);

        if ($cancelActiveTransaction) {
            $this->beginTransaction();
        }
    }

    /**
     * @param bool $cancelActiveTransaction
     */
    public function loadFixtures($cancelActiveTransaction = false)
    {
        if ($cancelActiveTransaction) {
            $this->rollbackTransaction();
        }

        $fixtureLoader = new DoctrineFixtureLoader($this->getClient());
        $executor = $fixtureLoader->load($this->fixturesList);

        $this->referenceRepository = $executor->getReferenceRepository();

        $this->clearDriverCache();

        if ($cancelActiveTransaction) {
            $this->beginTransaction();
        }
    }

    private function clearDriverCache()
    {
        /* @var $manager ObjectManager */
        $manager = $this->_getReferenceRepository()->getManager();

        /* @var $metadataFactory ClassMetadataFactory */
        $metadataFactory = $manager->getMetadataFactory();

        /* @var $cacheDriver CacheProvider */
        $cacheDriver = $metadataFactory->getCacheDriver();

        if ($cacheDriver) {
            $cacheDriver->deleteAll();
        }
    }

    /**
     * @return ReferenceRepository
     */
    public function _getReferenceRepository()
    {
        return $this->referenceRepository;
    }

    /**
     * @return array
     */
    protected function getDataFixturesPaths()
    {
        $paths = array();
        foreach ($this->getKernel()->getBundles() as $bundle) {
            $paths[] = $bundle->getPath().'/DataFixtures/ORM';
        }

        return $paths;
    }

    /**
     * @return ContainerInterface
     */
    private function getContainer()
    {
        return $this->getKernel()->getContainer();
    }

    /**
     * @return Client
     */
    private function getClient()
    {
        return $this->getContainer()->get('test.client');
    }

    /**
     * @return Kernel
     */
    private function getKernel()
    {
        /* @var $sf2 Symfony2Module */
        $sf2 = $this->getModule('Symfony2');
        $sf2->kernel->boot();

        return $sf2->kernel;
    }
}

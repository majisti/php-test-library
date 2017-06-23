<?php
/**
 * @copyright 2012 Instaclick Inc.
 */

namespace Majisti\Testing\Fixtures;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\DBAL\Driver\PDOSqlite\Driver as SqliteDriver;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Schema Loader
 * Implementation inspired by LiipFunctionalTestBundle
 *
 * @author Juti Noppornpitak <jnopporn@shiroyuki.com>
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class CachedSchemaLoader
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var string
     */
    private $cacheDirectory;

    /**
     * Constructor.
     *
     * @param EntityManager $entityManager
     * @param $cacheDirectory
     */
    public function __construct(EntityManager $entityManager, $cacheDirectory)
    {
        $this->entityManager = $entityManager;
        $this->cacheDirectory = $cacheDirectory;
    }

    /**
     * Retrieve the associated EntityManager.
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * Load the Database Schema.
     *
     * @param int $purgeMode
     */
    public function load($purgeMode = ORMPurger::PURGE_MODE_TRUNCATE)
    {
        $connection = $this->entityManager->getConnection();

        switch (true) {
            case $connection->getDriver() instanceof SqliteDriver:
                $this->loadSqliteSchema();
                break;
            default:
                $purger = new ORMPurger($this->entityManager);
                $purger->setPurgeMode($purgeMode);

                $executor = new ORMExecutor($this->entityManager, $purger);
                $executor->setReferenceRepository(new ReferenceRepository($this->entityManager));

                $executor->purge();
                break;
        }
    }

    /**
     * Load SQLite Driver Schema.
     */
    private function loadSqliteSchema()
    {
        $connection = $this->entityManager->getConnection();
        $metadataList = $this->entityManager->getMetadataFactory()->getAllMetadata();

        $parameters = $connection->getParams();
        $database = isset($parameters['path']) ? $parameters['path'] : $parameters['dbname'];
        $backupDatabase = sprintf('%s/test_%s.db', $this->cacheDirectory, md5(serialize($metadataList)));

        if (!file_exists($backupDatabase)) {
            $schemaTool = new SchemaTool($this->entityManager);

            $schemaTool->dropDatabase();
            $schemaTool->createSchema($metadataList);

            // Flip the database saving process. The actual one as the primary
            $tmpDatabase = $database;
            $database = $backupDatabase;
            $backupDatabase = $tmpDatabase;
        }

        copy($backupDatabase, $database);
    }
}

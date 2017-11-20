<?php

declare(strict_types=1);

namespace Majisti\Testing\Database;

use Symfony\Component\Filesystem\Filesystem;

/**
 * Creates an empty database (schema-only) and caches it with the filesystem under a unique name.
 *
 * Implementation inspired by ICTestCaseBundle & LiipFunctionalTestBundle.
 *
 * @author Steven Rosato <steven.rosato@majisti.com>
 */
class CachedSqliteSchemaLoader
{
    /**
     * @var string
     */
    private $cacheDirectory;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var PersistenceProvider
     */
    private $persistenceProvider;

    public function __construct(PersistenceProvider $persistenceProvider, Filesystem $filesystem, string $cacheDirectory)
    {
        $this->persistenceProvider = $persistenceProvider;
        $this->filesystem = $filesystem;
        $this->cacheDirectory = $cacheDirectory;
    }

    public function load(): void
    {
        if (!$this->persistenceProvider->isCurrentDriverSqlite()) {
            throw new \LogicException('Only the Sqlite driver is supported');
        }

        $metadataList = $this->persistenceProvider->getMetadata();

        $parameters = $this->persistenceProvider->getConnectionParameters();
        $database = isset($parameters['path']) ? $parameters['path'] : $parameters['dbname'];
        $backupDatabase = sprintf('%s/%s_schema_%s.sqlite', $this->cacheDirectory, $parameters['dbname'], md5(serialize($metadataList)));

        if (!$this->filesystem->exists($backupDatabase)) {
            $schemaTool = $this->persistenceProvider->createSchemaTool();
            $schemaTool->dropDatabase();
            $schemaTool->createSchema($metadataList);

            // Flip the database saving process. We will create a backup of the current database
            $tmpDatabase = $database;
            $database = $backupDatabase;
            $backupDatabase = $tmpDatabase;
        }

        $this->filesystem->copy($backupDatabase, $database, true);
    }
}

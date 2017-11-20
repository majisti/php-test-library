<?php

declare(strict_types=1);

namespace Majisti\Testing\Database;

use Majisti\Testing\Fixtures\FixturesLoader;

class DatabaseCacheCoordinator
{
    /**
     * @var FixturesLoader
     */
    private $fixturesLoader;

    /**
     * @var string
     */
    private $cacheDirectory;

    /**
     * @var string
     */
    private $logsDirectory;

    /**
     * @var CachedSqliteSchemaLoader
     */
    private $schemaLoader;

    /**
     * @var SqliteDatabaseBackupTool
     */
    private $backupTool;

    public function __construct(FixturesLoader $fixturesLoader, CachedSqliteSchemaLoader $schemaLoader,
        SqliteDatabaseBackupTool $backupTool, string $cacheDirectory, string $logsDirectory
    ) {
        $this->fixturesLoader = $fixturesLoader;
        $this->schemaLoader = $schemaLoader;
        $this->backupTool = $backupTool;
        $this->cacheDirectory = $cacheDirectory;
        $this->logsDirectory = $logsDirectory;
    }

    public function createCachedSchema(): void
    {
        $this->schemaLoader->load();
    }

    public function loadFullData(): void
    {
        $this->loadCachedApplicationFixtures();
    }

    protected function loadCachedApplicationFixtures(): void
    {
        $this->backupTool->setBackupDirectory($this->cacheDirectory);
        $backupName = 'populated';

        if (!$this->backupTool->isAlreadyBackedUp($backupName)) {
            $this->fixturesLoader->loadFullFixtures();
            $this->backupTool->backupDatabase($backupName);
        } else {
            $this->backupTool->restoreDatabase($backupName);
        }
    }

    public function snapshotDatabase(): void
    {
        $this->snapshotDatabaseWithName('snapshot');
    }

    public function snapshotDatabaseWithName(string $name): void
    {
        $this->backupTool
            ->setBackupDirectory($this->logsDirectory)
            ->backupDatabase($name);
    }
}

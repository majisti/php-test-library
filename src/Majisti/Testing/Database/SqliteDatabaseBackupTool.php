<?php

declare(strict_types=1);

namespace Majisti\Testing\Database;

use Doctrine\ORM\EntityManagerInterface;
use Majisti\Testing\Utilities\FriendlyPathBuilder;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Creates sqlite database backups.
 *
 * Implementation inspired by ICTestCaseBundle & LiipFunctionalTestBundle.
 *
 * @author Steven Rosato <steven.rosato@majisti.com>
 */
class SqliteDatabaseBackupTool
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var bool
     */
    private $transactional = false;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $backupDirectory;

    /**
     * @var FriendlyPathBuilder
     */
    private $friendlyPathBuilder;

    public function __construct(EntityManagerInterface $entityManager, Filesystem $filesystem, string $backupDirectory)
    {
        $this->entityManager = $entityManager;
        $this->filesystem = $filesystem;
        $this->backupDirectory = $backupDirectory;
    }

    public function getBackupDirectory(): string
    {
        return $this->backupDirectory;
    }

    public function setBackupDirectory(string $backupDirectory): self
    {
        $this->backupDirectory = $backupDirectory;

        return $this;
    }

    public function getFriendlyPathBuilder(): FriendlyPathBuilder
    {
        if (null === $this->friendlyPathBuilder) {
            $this->friendlyPathBuilder = new FriendlyPathBuilder();
        }

        return $this->friendlyPathBuilder;
    }

    public function setFriendlyPathBuilder(FriendlyPathBuilder $friendlyPathBuilder): void
    {
        $this->friendlyPathBuilder = $friendlyPathBuilder;
    }

    public function setShouldHandleTransactions(bool $shouldHandleTransactions): SqliteDatabaseBackupTool
    {
        $this->transactional = $shouldHandleTransactions;

        return $this;
    }

    public function backupDatabase(string $name): void
    {
        $connection = $this->entityManager->getConnection();

        if ($this->transactional && $connection->isTransactionActive() && $connection->isRollbackOnly()) {
            return;
        }

        $databasePath = $this->getDatabasePath();

        $tempBackupDatabasePath = $this->getNamedDatabasePath('backup');
        $backupDatabasePath = $this->getFriendlyPath($this->getNamedBackupDatabasePath($name));

        $this->filesystem->copy($databasePath, $tempBackupDatabasePath);

        if ($this->transactional && $connection->isTransactionActive()) {
            $this->entityManager->commit();
        }

        $this->filesystem->copy($databasePath, $backupDatabasePath);
        $this->filesystem->copy($tempBackupDatabasePath, $databasePath);
        $this->filesystem->remove($tempBackupDatabasePath);

        if ($this->transactional) {
            $this->entityManager->beginTransaction();
        }
    }

    public function restoreDatabase(string $name): void
    {
        $backedUpDatabasePath = $this->getNamedBackupDatabasePath($name);
        $databasePath = $this->getDatabasePath();

        $this->filesystem->copy($backedUpDatabasePath, $databasePath, true);
    }

    public function isAlreadyBackedUp(string $name)
    {
        return $this->filesystem->exists($this->getNamedBackupDatabasePath($name));
    }

    private function getDatabasePath()
    {
        return $this->entityManager->getConnection()->getParams()['path'];
    }

    private function getDatabaseName()
    {
        return $this->entityManager->getConnection()->getParams()['dbname'];
    }

    private function getNamedDatabasePath(string $name)
    {
        $databasePathParts = pathinfo($this->getDatabasePath());

        return sprintf(
            '%s/%s_%s.%s',
            $databasePathParts['dirname'],
            $this->getDatabaseName(),
            $name,
            $databasePathParts['extension']
        );
    }

    private function getNamedBackupDatabasePath(string $name)
    {
        $databasePathParts = pathinfo($this->getDatabasePath());

        return sprintf(
            '%s/%s_%s.%s',
            $this->backupDirectory,
            $this->getDatabaseName(),
            $name,
            $databasePathParts['extension']
        );
    }

    protected function getFriendlyPath(string $name): string
    {
        $name = $this->getFriendlyPathBuilder()
            ->buildDefaultFriendlyPath($name)
            ->getPath();

        return $name;
    }
}

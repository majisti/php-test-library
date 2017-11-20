<?php

declare(strict_types=1);

namespace Majisti\Testing\Database;

use Doctrine\DBAL\Driver;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Connection as DriverConnection;
use Doctrine\DBAL\Driver\PDOSqlite\Driver as SqliteDriver;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Tools\SchemaTool;

class PersistenceProvider
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return ClassMetadata[]
     */
    public function getMetadata(): array
    {
        return $this->entityManager->getMetadataFactory()->getAllMetadata();
    }

    public function getDriverConnection(): DriverConnection
    {
        return $this->entityManager->getConnection();
    }

    private function getCurrentDriver(): Driver
    {
        return $this->getDriverConnection()->getDriver();
    }

    public function isCurrentDriverSqlite(): bool
    {
        return $this->getCurrentDriver() instanceof SqliteDriver;
    }

    public function getConnectionParameters(): array
    {
        return $this->getDriverConnection()->getParams();
    }

    public function createSchemaTool(): SchemaTool
    {
        return new SchemaTool($this->entityManager);
    }
}

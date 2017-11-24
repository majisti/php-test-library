<?php

declare(strict_types=1);

namespace Majisti\Testing\Bridge\Behat;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Majisti\Testing\Database\DatabaseCacheCoordinator;

/**
 * This context is in charge of reloading the database schema and fixtures. It caches either
 * the schema or the fixtures in order for tests to run much faster after the first run.
 *
 * @author Steven Rosato <steven.rosato@majisti.com>
 */
class DatabaseContext implements Context, KernelAwareContext
{
    use KernelDictionary;
    use ScenarioHelperTrait;

    /**
     * @var DatabaseCacheCoordinator
     */
    private $databaseCacheCoordinator;

    public function __construct(DatabaseCacheCoordinator $databaseCacheCoordinator)
    {
        $this->databaseCacheCoordinator = $databaseCacheCoordinator;
    }

    /**
     * @BeforeScenario
     */
    public function createCachedSchema(): void
    {
        $this->databaseCacheCoordinator->createCachedSchema();
    }

    /**
     * @BeforeScenario @withFullData
     */
    public function loadFullData(): void
    {
        $this->databaseCacheCoordinator->loadFullData();
    }

    /**
     * @Given /^I snapshot the database$/
     */
    public function snapshotDatabase(): void
    {
        $this->databaseCacheCoordinator->snapshotDatabaseWithName('snapshot');
    }

    /**
     * @Given /^I snapshot the database with name "([^"]*)"$/
     */
    public function snapshotDatabaseWithName(string $name): void
    {
        $this->databaseCacheCoordinator->snapshotDatabaseWithName($name);
    }

    /**
     * @AfterStep
     */
    public function snapshotDatabaseOnFailure(AfterStepScope $scope): void
    {
        if (!$scope->getTestResult()->isPassed()) {
            $featureFilename = pathinfo($scope->getFeature()->getFile(), PATHINFO_FILENAME);
            $this->databaseCacheCoordinator->snapshotDatabaseWithName(
                sprintf('%s_%s', $featureFilename, $this->getScenario($scope)->getTitle())
            );
        }
    }
}

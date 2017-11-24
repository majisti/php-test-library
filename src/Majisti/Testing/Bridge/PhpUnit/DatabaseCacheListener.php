<?php

declare(strict_types=1);

namespace Majisti\Testing\Bridge\PhpUnit;

use Exception;
use Majisti\Testing\Database\DatabaseCacheCoordinator;
use Majisti\Testing\KernelAwareTest;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestListener;
use PHPUnit_Framework_AssertionFailedError;
use PHPUnit_Framework_Test;
use PHPUnit_Framework_TestSuite;

class DatabaseCacheListener implements TestListener
{
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time): void
    {
        $this->snapshotDatabaseIfPossible($test);
    }

    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time): void
    {
        $this->snapshotDatabaseIfPossible($test);
    }

    private function snapshotDatabaseIfPossible(PHPUnit_Framework_Test $test)
    {
        if ($test instanceof TestCase && $coordinator = $this->getDatabaseCacheCoordinator($test)) {
            $className = str_replace('\\', '_', get_class($test));
            $coordinator->snapshotDatabaseWithName(sprintf('%s_%s', $className, $test->getName()));
        }
    }

    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time): void
    {
    }

    public function addRiskyTest(PHPUnit_Framework_Test $test, Exception $e, $time): void
    {
    }

    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time): void
    {
    }

    public function startTestSuite(PHPUnit_Framework_TestSuite $suite): void
    {
    }

    public function endTestSuite(PHPUnit_Framework_TestSuite $suite): void
    {
    }

    public function startTest(PHPUnit_Framework_Test $test): void
    {
        if ($coordinator = $this->getDatabaseCacheCoordinator($test)) {
            $coordinator->createCachedSchema();
            $coordinator->loadFullData();
        }
    }

    public function endTest(PHPUnit_Framework_Test $test, $time): void
    {
    }

    private function getDatabaseCacheCoordinator(PHPUnit_Framework_Test $test): ?DatabaseCacheCoordinator
    {
        if ($test instanceof KernelAwareTest && !$test->isSkippingKernelBootingOnSetup()) {
            $test->initKernel();

            return $test->getContainer()->get(DatabaseCacheCoordinator::class);
        }

        return null;
    }
}

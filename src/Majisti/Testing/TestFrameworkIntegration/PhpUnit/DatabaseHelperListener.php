<?php

namespace Majisti\Testing\TestFrameworkIntegration\PhpUnit;

use Exception;
use Majisti\Testing\Database\DatabaseHelper;
use Majisti\Testing\KernelAwareTest;
use PHPUnit\Framework\TestListener;
use PHPUnit_Framework_AssertionFailedError;
use PHPUnit_Framework_Test;
use PHPUnit_Framework_TestSuite;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Will help with fixtures reloading for tests supporting fixtures with a Symfony Kernel.
 *
 * @author Steven Rosato
 */
class DatabaseHelperListener implements TestListener
{
    /**
     * An error occurred.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception $e
     * @param float $time
     */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
    }

    /**
     * A failure occurred.
     *
     * @param PHPUnit_Framework_Test $test
     * @param PHPUnit_Framework_AssertionFailedError $e
     * @param float $time
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
    }

    /**
     * Incomplete test.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception $e
     * @param float $time
     */
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
    }

    /**
     * Risky test.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception $e
     * @param float $time
     */
    public function addRiskyTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
    }

    /**
     * Skipped test.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception $e
     * @param float $time
     */
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
    }

    /**
     * A test suite started.
     *
     * @param PHPUnit_Framework_TestSuite $suite
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
    }

    /**
     * A test suite ended.
     *
     * @param PHPUnit_Framework_TestSuite $suite
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
    }

    /**
     * A test started.
     *
     * @param PHPUnit_Framework_Test $test
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
        if ($test instanceof KernelAwareTest && !$test->isSkippingKernelBooting()) {
            $this->createDatabaseHelper($this->getBootedKernel($test))->beforeTest($test);
        }
    }

    /**
     * A test ended.
     *
     * @param PHPUnit_Framework_Test $test
     * @param float $time
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
    }

    protected function createDatabaseHelper(KernelInterface $kernel)
    {
        $databaseHelper = new DatabaseHelper($kernel);
        $kernel->getContainer()->get('app.test.helper')->setDatabaseHelper($databaseHelper);

        return $databaseHelper;
    }

    private function getBootedKernel(KernelAwareTest $test)
    {
        $kernel = $test->getKernel();
        $kernel->boot();

        return $kernel;
    }
}

<?php

namespace Majisti\Testing\Database;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Will help with fixtures loading and caching, as well as manipulating the test database while doing tests.
 *
 * @author Steven Rosato
 */
class DatabaseHelper
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Kernel
     */
    private $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $this->container = $kernel->getContainer();
    }

    /**
     * @param TestCase $test
     */
    public function beforeTest(TestCase $test)
    {
        $this->loadFixtures();
    }

    /**
     * @param TestCase $test
     */
    public function afterTest(TestCase $test)
    {
    }

    public function loadFixtures()
    {
        $this->getContainer()->get('majisti.fixtures.cached_loader')->load();
    }

    /**
     * @return ContainerInterface
     */
    private function getContainer()
    {
        return $this->getKernel()->getContainer();
    }

    private function getKernel()
    {
        return $this->kernel;
    }
}

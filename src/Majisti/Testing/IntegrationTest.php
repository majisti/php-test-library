<?php

namespace Majisti\Testing;

use Majisti\Testing\Database\DatabaseCacheCoordinator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Client;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class IntegrationTest extends KernelTestCase
{
    use Hamcrest;

    protected $shouldSkipKernelBooting = false;

    /**
     * @var ContainerInterface
     */
    protected $container;

    protected function setUp()
    {
        parent::setUp();

        if (!$this->shouldSkipKernelBooting) {
            static::bootKernel();
            $this->container = static::$kernel->getContainer();
        }
    }

    public function getKernel(): KernelInterface
    {
        return static::$kernel;
    }

    public function getDatabaseCacheCoordinator(): DatabaseCacheCoordinator
    {
        return $this->getContainer()->get(DatabaseCacheCoordinator::class);
    }

    public function getConsoleTester(): ConsoleTester
    {
        return $this->getContainer()->get(ConsoleTester::class);
    }

    public function getConsoleDisplay(): string
    {
        return $this->getConsoleTester()->getConsoleDisplay();
    }

    public function runConsoleCommand(array $input, array $options = []): int
    {
        return $this->getConsoleTester()->runCommandFromArray($input, $options);
    }

    public function getSymfonyClient(): Client
    {
        return $this->getContainer()->get('test.client');
    }
}

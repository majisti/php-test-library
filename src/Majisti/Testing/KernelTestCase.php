<?php

declare(strict_types=1);

namespace Majisti\Testing;

use Majisti\Testing\Fixtures\FixturesLoader;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase as BaseKernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class KernelTestCase extends BaseKernelTestCase implements KernelAwareTest
{
    protected $shouldSkipKernelBooting = false;

    protected $kernelAlreadyBooted = false;

    /**
     * @var ContainerInterface
     */
    protected $container;

    protected function setUp()
    {
        parent::setUp();
        $this->initKernel();
    }

    public function initKernel(): void
    {
        if (!$this->shouldSkipKernelBooting && !$this->kernelAlreadyBooted) {
            static::bootKernel();
            $this->kernelAlreadyBooted = true;
            $this->container = static::$kernel->getContainer();
        }
    }

    public function getContainer(): ?ContainerInterface
    {
        return $this->container;
    }

    public function getKernel(): ?KernelInterface
    {
        return static::$kernel;
    }

    public function getFixturesLoader(): FixturesLoader
    {
        /* @var $loader FixturesLoader */
        $loader = $this->getContainer()->get(FixturesLoader::class);

        return $loader;
    }

    public function isSkippingKernelBootingOnSetup(): bool
    {
        return $this->shouldSkipKernelBooting;
    }
}

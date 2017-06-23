<?php

namespace Majisti\Testing;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase as BaseKernelTestCase;

abstract class KernelTestCase extends BaseKernelTestCase
{
    use Hamcrest;

    protected $skipKernelBoot = false;

    public static function setUpBeforeClass()
    {
        static::$kernel = static::createKernel();
    }

    public function isSkippingKernelBooting()
    {
        return $this->skipKernelBoot;
    }

    protected function setUp()
    {
        parent::setUp();

        if (!$this->skipKernelBoot) {
            $this->getKernel()->boot();
        }
    }

    public function getKernel()
    {
        return static::$kernel;
    }

    public function getContainer()
    {
        return $this->getKernel()->getContainer();
    }
}
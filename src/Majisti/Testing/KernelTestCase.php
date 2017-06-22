<?php

namespace Majisti\Testing;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase as BaseKernelTestCase;

abstract class KernelTestCase extends BaseKernelTestCase
{
    use Hamcrest;

    public static function setUpBeforeClass()
    {
        static::$kernel = static::createKernel();
    }

    protected function setUp()
    {
        parent::setUp();

        $this->getKernel()->boot();
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
<?php

namespace Tests\Component;

use Majisti\Testing\ComponentTest;

class FixturesLoadingSkipTest extends ComponentTest
{
    protected $skipKernelBoot = true;

    protected function setUp()
    {
        parent::setUp();
    }

    public function testKernelShouldNotBeBooted()
    {
        $kernel = $this->getKernel();
        $this->verifyThat($kernel, is(notNullValue()));
        $this->verifyThat($kernel->getContainer(), is(nullValue()));
    }
}
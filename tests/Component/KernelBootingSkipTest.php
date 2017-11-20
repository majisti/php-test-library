<?php

namespace Tests\Component;

use Majisti\Testing\ComponentTest;

class KernelBootingSkipTest extends ComponentTest
{
    protected $shouldSkipKernelBooting = true;

    protected function setUp()
    {
        parent::setUp();
    }

    public function testKernelShouldNotBeBooted()
    {
        if($this->getKernel()) {
            $this->verifyThat($this->getContainer(), is(nullValue()));
        }
    }
}

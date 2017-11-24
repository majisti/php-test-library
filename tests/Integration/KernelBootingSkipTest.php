<?php

namespace Tests\Integration;

use Majisti\Testing\IntegrationTest;

class KernelBootingSkipTest extends IntegrationTest
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

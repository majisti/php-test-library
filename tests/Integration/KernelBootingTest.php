<?php

namespace Tests\Integration;

use Majisti\Testing\IntegrationTest;

class KernelBootingTest extends IntegrationTest
{
    public function testKernelShouldAlreadyBeBooted()
    {
        $kernel = $this->getKernel();
        $this->verifyThat($kernel, is(notNullValue()));
        $this->verifyThat($this->getContainer(), is(notNullValue()));
    }
}

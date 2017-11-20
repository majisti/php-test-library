<?php

namespace Tests\Component;

use Majisti\Testing\ComponentTest;

class KernelBootingTest extends ComponentTest
{
    public function testKernelShouldAlreadyBeBooted()
    {
        $kernel = $this->getKernel();
        $this->verifyThat($kernel, is(notNullValue()));
        $this->verifyThat($this->getContainer(), is(notNullValue()));
    }
}

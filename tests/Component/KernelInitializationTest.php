<?php

namespace Tests\Component;

use Majisti\Testing\ComponentTest;

class KernelInitializationTest extends ComponentTest
{
    public function testKernelShouldAlreadyBeBooted()
    {
        $kernel = $this->getKernel();
        $this->verifyThat($kernel, is(notNullValue()));
        $this->verifyThat($kernel->getContainer(), is(notNullValue()));
    }
}

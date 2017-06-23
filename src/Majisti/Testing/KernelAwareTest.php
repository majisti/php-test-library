<?php

namespace Majisti\Testing;

use Symfony\Component\HttpKernel\KernelInterface;

interface KernelAwareTest
{
    /**
     * @return KernelInterface
     */
    public function getKernel();

    public function isSkippingKernelBooting();
}
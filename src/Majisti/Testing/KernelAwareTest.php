<?php

namespace Majisti\Testing;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

interface KernelAwareTest
{
    public function initKernel();

    /**
     * @return KernelInterface
     */
    public function getKernel(): ?KernelInterface;

    public function getContainer(): ?ContainerInterface;

    public function isSkippingKernelBootingOnSetup(): bool;
}
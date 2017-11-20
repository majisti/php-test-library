<?php

declare(strict_types=1);

namespace Majisti\Testing;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\ApplicationTester;
use Symfony\Component\HttpKernel\KernelInterface;

class ConsoleTester
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var ApplicationTester
     */
    private $applicationTester;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    public function getApplicationTester()
    {
        if (null === $this->applicationTester) {
            $application = new Application($this->kernel);
            $application->setAutoExit(false);
            $this->applicationTester = new ApplicationTester($application);
        }

        return $this->applicationTester;
    }

    public function getConsoleDisplay(): string
    {
        return $this->getApplicationTester()->getDisplay();
    }

    /**
     * @return int The console exit code
     */
    public function runCommandFromArray(array $input, array $options = []): int
    {
        $options = array_merge($this->getDefaultOptions(), $options);

        return $this->getApplicationTester()->run($input, $options);
    }

    private function getDefaultOptions(): array
    {
        return [
            'interactive' => false,
        ];
    }
}

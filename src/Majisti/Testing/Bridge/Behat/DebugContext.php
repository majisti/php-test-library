<?php

declare(strict_types=1);

namespace Majisti\Testing\Bridge\Behat;

use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Mink\Exception\DriverException;
use Behatch\Context\BaseContext;
use Behatch\HttpCall\HttpCallResultPool;

class DebugContext extends BaseContext
{
    /**
     * @var HttpCallResultPool
     */
    private $httpCallResultPool;

    public function __construct(HttpCallResultPool $httpCallResultPool)
    {
        $this->httpCallResultPool = $httpCallResultPool;
    }

    /**
     * @AfterStep
     */
    public function verboseResponseOnFailedStep(AfterStepScope $scope): void
    {
        if (!$scope->getTestResult()->isPassed()) {
            try {
                $this->getMinkContext()->showLastResponse(); //based on our config, this will save an HTML file within the logs folder
            } catch (DriverException $e) {
            }

            if ($httpResult = $this->httpCallResultPool->getResult()) {
                throw new \Exception('HTTP result output was: '.$httpResult->getValue());
            }
        }
    }
}

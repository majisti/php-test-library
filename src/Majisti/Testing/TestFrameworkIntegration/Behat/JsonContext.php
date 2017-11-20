<?php

declare(strict_types=1);

namespace Tests\Acceptance\Context;

use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behatch\Context\JsonContext as BaseJsonContext;
use Behatch\Context\RestContext;
use Behatch\Json\JsonSchema;
use Symfony\Component\HttpFoundation\Response;

class JsonContext extends BaseJsonContext
{
    /**
     * @var RestContext
     */
    private $restContext;

    public function validateJsonSchemaArray(array $schema): void
    {
        $this->inspector->validate($this->getJson(), new JsonSchema(json_encode($schema)));
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope): void
    {
        /* @var $environment InitializedContextEnvironment */
        $environment = $scope->getEnvironment();

        $this->restContext = $environment->getContext(RestContext::class);
    }

    /**
     * @Then /^the response should be valid jsonld$/
     */
    public function theResponseShouldBeValidJsonLd(): void
    {
        $this->getMinkContext()->assertResponseStatus(Response::HTTP_OK);
        $this->theResponseShouldBeInJson();
        $this->restContext->theHeaderShouldBeEqualTo('Content-type', 'application/ld+json; charset=utf-8');
    }
}

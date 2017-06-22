<?php

namespace Majisti\Testing;

use PSS\SymfonyMockerContainer\DependencyInjection\MockerContainer;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class FunctionalTest extends WebTestCase
{
    use Hamcrest;

    protected $client;

    protected function getClient()
    {
        if (null === $this->client) {
            $this->client = $this->createClient();
            $this->client->followRedirects(true);
        }

        return $this->client;
    }

    /**
     * @return MockerContainer
     */
    protected function getContainer()
    {
        return $this->getClient()->getContainer();
    }

    protected function getRouter()
    {
        return $this->getContainer()->get('router');
    }
}

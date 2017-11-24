<?php

declare(strict_types=1);

namespace Majisti\Testing\Bridge\Behat;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Majisti\Testing\Fixtures\DoctrineFixturesLoader;
use Majisti\Testing\Fixtures\FixturesLoader;
use Symfony\Component\Yaml\Yaml;

/**
 * This context will take charge of loading fixtures, either all of them or in a more granular way for ease of testing.
 */
class FixturesContext implements Context, KernelAwareContext
{
    use KernelDictionary;

    /**
     * @var FixturesLoader
     */
    private $fixturesLoader;

    public function __construct(DoctrineFixturesLoader $fixturesLoader)
    {
        $this->fixturesLoader = $fixturesLoader;
    }

    public function loadFullFixtures(): void
    {
        $this->fixturesLoader->loadFullFixtures();
    }

    /**
     * @Given the following fixtures are loaded:
     */
    public function loadAliceFixturesFromPyString(PyStringNode $aliceYamlStr): void
    {
        $this->fixturesLoader->loadFixturesFromAliceYamlString(Yaml::parse($aliceYamlStr->getRaw()));
    }
}

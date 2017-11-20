<?php

namespace Majisti\Testing\Fixtures;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Majisti\Testing\Database\ORMExecutor;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader as DataFixturesLoader;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PhpFixturesLoader
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ReferenceRepository
     */
    protected $referenceRepository;

    /**
     * @var ORMExecutor
     */
    protected $executor;

    public function __construct(ContainerInterface $container, ORMExecutor $executor)
    {
        $this->container = $container;
        $this->executor = $executor;
    }

    public function setReferenceRepository(ReferenceRepository $referenceRepository)
    {
        $this->referenceRepository = $referenceRepository;
    }

    public function loadPhpFixtureFileName(string $fixturePath, bool $append = true): void
    {
        $fixturesLoader = new DataFixturesLoader($this->container);
        $fixturesLoader->loadFromFile($fixturePath);

        $this->executePhpFixtures($fixturesLoader, $append);
    }

    public function loadPhpFixtureClasses(array $fixtures, bool $append = true)
    {
        if (!empty($fixtures)) {
            $fixturesLoader = new DataFixturesLoader($this->container);

            foreach ($fixtures as $fixture) {
                $fixturesLoader->addFixture($fixture);
            }

            $this->executePhpFixtures($fixturesLoader, $append);
        }
    }

    protected function executePhpFixtures(DataFixturesLoader $fixturesLoader, bool $append)
    {
        $this->restoreReferences();
        $this->executor->execute($fixturesLoader->getFixtures(), $append);
        $this->saveReferences();
    }

    protected function restoreReferences()
    {
        if ($this->referenceRepository) {
            foreach ($this->referenceRepository->getReferences() as $name => $object) {
                $this->executor->getReferenceRepository()->setReference($name, $object);
            }
        }
    }

    protected function saveReferences()
    {
        if ($this->referenceRepository) {
            foreach ($this->executor->getReferenceRepository()->getReferences() as $name => $object) {
                $this->referenceRepository->setReference($name, $object);
            }
        }
    }
}
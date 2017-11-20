<?php

namespace Majisti\Testing\Fixtures;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;

abstract class DoctrineFixturesLoader implements FixturesLoader
{
    /**
     * @var AliceFixturesLoader
     */
    protected $aliceFixturesLoader;

    /**
     * @var PhpFixturesLoader
     */
    protected $phpFixturesLoader;

    /**
     * @var ReferenceRepository
     */
    protected $referenceRepository;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager, AliceFixturesLoader $aliceFixturesLoader, PhpFixturesLoader $phpFixturesLoader)
    {
        $this->entityManager = $entityManager;
        $this->aliceFixturesLoader = $aliceFixturesLoader;
        $this->phpFixturesLoader = $phpFixturesLoader;

        $this->phpFixturesLoader->setReferenceRepository($this->getReferenceRepository());
        $this->aliceFixturesLoader->setReferenceRepository($this->getReferenceRepository());
    }

    public function getReferenceRepository(): ReferenceRepository
    {
        if (null === $this->referenceRepository) {
            $this->referenceRepository = new ReferenceRepository($this->entityManager);
        }

        return $this->referenceRepository;
    }

    public function loadFixturesFromAliceYamlString(string $yamlStr)
    {
        $this->aliceFixturesLoader->loadAliceFixtureFileOrData($yamlStr);
    }
}
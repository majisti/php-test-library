<?php

namespace Majisti\Testing\Fixtures;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Hautelook\AliceBundle\Alice\DataFixtures\Fixtures\Loader as BaseAliceFixturesLoader;
use Nelmio\Alice\Persister\Doctrine;

class AliceFixturesLoader
{
    /**
     * @var BaseAliceFixturesLoader
     */
    private $aliceFixturesLoader;

    /**
     * @var Doctrine
     */
    private $persister;

    /**
     * @var ReferenceRepository
     */
    private $referenceRepository;

    public function __construct(BaseAliceFixturesLoader $aliceFixturesLoader, Doctrine $persister)
    {
        $this->aliceFixturesLoader = $aliceFixturesLoader;
        $this->persister = $persister;
    }

    public function setReferenceRepository(ReferenceRepository $referenceRepository)
    {
        $this->referenceRepository = $referenceRepository;
    }

    public function loadFixtureFile(string $yamlPath): void
    {
        $this->loadAliceFixtureFileOrData($yamlPath);
    }

    public function loadAliceFixtureFileOrData(string $filePathOrData): void
    {
        $objects = $this->aliceFixturesLoader->load($filePathOrData, $this->referenceRepository->getReferences());
        $this->persister->persist($objects);

        if ($this->referenceRepository) {
            foreach ($objects as $name => $object) {
                $this->referenceRepository->addReference($name, $object);
            }
        }
    }

}
<?php

namespace Tests\Component\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Tests\Utils\ComponentTest;

abstract class RepositoryTest extends ComponentTest
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function setUp()
    {
        parent::setUp();

        $this->entityManager = $this->getContainer()->get('doctrine.orm.default_entity_manager');
    }

    abstract protected function getRepository();
}
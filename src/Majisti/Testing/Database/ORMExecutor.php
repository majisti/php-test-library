<?php

declare(strict_types=1);

namespace Majisti\Testing\Database;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor as BaseORMExecutor;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\SharedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * A little hack here because we do not want to clear the EntityManager after loading the fixtures, in case we
 * are sharing that entity manager for subsequent fixtures loading with other libraries such as Alice.
 */
class ORMExecutor extends BaseORMExecutor
{
    /**
     * We override this method because we do not want to clear the manager.
     *
     * @param ObjectManager $manager
     * @param FixtureInterface $fixture
     */
    public function load(ObjectManager $manager, FixtureInterface $fixture): void
    {
        if ($this->logger) {
            $prefix = '';
            if ($fixture instanceof OrderedFixtureInterface) {
                $prefix = sprintf('[%d] ', $fixture->getOrder());
            }
            $this->log('loading '.$prefix.get_class($fixture));
        }
        // additionally pass the instance of reference repository to shared fixtures
        if ($fixture instanceof SharedFixtureInterface) {
            $fixture->setReferenceRepository($this->referenceRepository);
        }
        $fixture->load($manager);
    }
}

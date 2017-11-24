<?php

namespace Tests\Integration;

use Majisti\Testing\IntegrationTest;

/**
 * @method FixturesReloadingTest uut()
 */
class FixturesReloadingTest extends IntegrationTest
{
    public function testRemoveInitiallyLoadedAliceFixtures()
    {
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $repo = $entityManager->getRepository('AcmeBundle:Profile');

        $this->verifyThat(count($repo->findAll()), is(greaterThan(0)));

        $repo->createQueryBuilder('p')
            ->delete()
            ->getQuery()
            ->execute()
        ;

        $this->verifyThat(count($repo->findAll()), is(0));
    }

    public function testAliceFixturesShouldBeAutomaticallyRestoredByDatabaseListener()
    {
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $repo = $entityManager->getRepository('AcmeBundle:Profile');

        $this->verifyThat(count($repo->findAll()), is(greaterThan(0)));
    }
}

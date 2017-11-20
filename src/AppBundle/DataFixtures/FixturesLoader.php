<?php

namespace AppBundle\DataFixtures;

use AppBundle\DataFixtures\ORM\Doctrine\FruitFixture;
use Majisti\Testing\Fixtures\DoctrineFixturesLoader;

class FixturesLoader extends DoctrineFixturesLoader
{
    public function loadFullFixtures()
    {
        $this->phpFixturesLoader->loadPhpFixtureClasses([new FruitFixture()]);
        $this->aliceFixturesLoader->loadFixtureFile(__DIR__ . '/ORM/profile.yml');
        $this->aliceFixturesLoader->loadFixtureFile(__DIR__ . '/ORM/user.yml');
    }
}
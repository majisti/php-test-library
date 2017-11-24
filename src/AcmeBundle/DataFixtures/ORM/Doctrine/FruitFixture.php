<?php

namespace AcmeBundle\DataFixtures\ORM\Doctrine;

use AcmeBundle\Entity\Fruit;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class FruitFixture implements FixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $fruits = [
            (new Fruit())->setName('apple'),
            (new Fruit())->setName('orange'),
        ];

        foreach ($fruits as $fruit) {
            $manager->persist($fruit);
        }

        $manager->flush();
    }
}

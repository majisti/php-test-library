<?php

namespace Majisti\Testing\Fixtures;

use Doctrine\Common\DataFixtures\ReferenceRepository;

interface FixturesLoader
{
    public function loadFullFixtures();

    public function getReferenceRepository(): ReferenceRepository;
}
<?php

namespace Majisti\Testing\Fixtures;

interface FixturesLoader
{
    /**
     * @return array
     */
    public function getFixturesList();

    public function load($append = false);
}

<?php

namespace Tests\Component;

use Majisti\Testing\ComponentTest;

class HelpersTest extends ComponentTest
{
    public function testTestHelperInitialized()
    {
        $testHelper = $this->getTestHelper();
        $this->verifyThat($testHelper, is(notNullValue()));
    }

    public function testDatabaseHelperShouldBeInitialized()
    {
        $databaseHelper = $this->getTestHelper()->getDatabaseHelper();
        $this->verifyThat($databaseHelper, is(notNullValue()));
    }
}

<?php

namespace Majisti\Testing;

abstract class ComponentTest extends KernelTestCase
{
    /**
     * @return TestHelper
     */
    public function getTestHelper()
    {
        return $this->getContainer()->get('app.test.helper');
    }
}

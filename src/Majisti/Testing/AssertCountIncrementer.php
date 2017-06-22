<?php

namespace Majisti\Testing;

use PHPUnit\Framework\Assert;

trait AssertCountIncrementer
{
    protected function incrementAssertionCounterByOne()
    {
        Assert::assertTrue(true);
    }
}

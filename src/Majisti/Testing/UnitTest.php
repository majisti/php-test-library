<?php

namespace Majisti\Testing;

use LogicException;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

abstract class UnitTest extends TestCase
{
    use Hamcrest;
    use MockeryPHPUnitIntegration;

    /**
     * The Unit Under Test.
     */
    protected $uut;

    /**
     * @throws LogicException If the unit under test cannot be found
     *
     * @return string
     */
    protected function createUnitUnderTest()
    {
        $rc = new \ReflectionClass($this);

        $className = $this->removeTestPrefixFromClassName($rc->getName());
        $className = $this->removeTestSuffixFromClassName($className);

        if (class_exists($className)) {
            return new $className();
        }

        throw new LogicException(sprintf('Class %s not found when trying to create unit under test.', $className));
    }

    protected function getUutNamespace()
    {
        $rc = new \ReflectionClass($this->uut());

        return $rc->getNamespaceName();
    }

    private function removeTestPrefixFromClassName($className)
    {
        $bundleName = $this->getBundleName();

        return preg_replace("/^Tests\\\Unit\\\({$bundleName}\\\.*)/", '$1', $className);
    }

    private function getBundleName()
    {
        $rc = new \ReflectionClass($this);

        $matches = [];
        if (preg_match('/([a-zA-Z]+Bundle)/', $rc->getFileName(), $matches)) {
            return $matches[1];
        }

        return '';
    }

    private function removeTestSuffixFromClassName($className)
    {
        return preg_replace('/Test$/', '', $className);
    }

    protected function uut()
    {
        if (!$this->uut) {
            $this->uut = $this->createUnitUnderTest();
        }

        return $this->uut;
    }
}

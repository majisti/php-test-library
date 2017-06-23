<?php

namespace Majisti\Testing\Fixtures;

use Doctrine\Common\Persistence\ObjectManager;
use Hautelook\AliceBundle\Alice\DataFixtures\Fixtures\Loader as BaseAliceFixturesLoader;
use Hautelook\AliceBundle\Alice\DataFixtures\Loader;
use Hautelook\AliceBundle\Doctrine\DataFixtures\Executor\FixturesExecutor;
use Hautelook\AliceBundle\Doctrine\Finder\FixturesFinder;
use Hautelook\AliceBundle\Doctrine\Generator\LoaderGenerator;
use Symfony\Component\HttpKernel\KernelInterface;

class AliceFixturesLoader implements FixturesLoader
{
    /**
     * @var FixturesFinder
     */
    private $fixturesFinder;

    /**
     * @var LoaderGenerator
     */
    private $loaderGenerator;

    /**
     * @var Loader
     */
    private $loader;

    /**
     * @var BaseAliceFixturesLoader
     */
    private $fixturesLoader;

    /**
     * @var FixturesExecutor
     */
    private $fixturesExecutor;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var KernelInterface
     */
    private $kernel;

    public function __construct(
        FixturesFinder $fixturesFinder,
        LoaderGenerator $loaderGenerator,
        Loader $loader,
        BaseAliceFixturesLoader $fixturesLoader,
        FixturesExecutor $fixturesExecutor,
        ObjectManager $objectManager,
        KernelInterface $kernel
    ) {
        $this->fixturesFinder = $fixturesFinder;
        $this->loaderGenerator = $loaderGenerator;
        $this->loader = $loader;
        $this->fixturesLoader = $fixturesLoader;
        $this->fixturesExecutor = $fixturesExecutor;
        $this->objectManager = $objectManager;
        $this->kernel = $kernel;
    }

    public function getFixturesList()
    {
        return $this->fixturesFinder->getFixtures(
            $this->kernel,
            $this->kernel->getBundles(),
            $this->kernel->getEnvironment()
        );
    }

    public function load($append = false)
    {
        $fixturesList = $this->getFixturesList();

        //FIXME: this will not take from cache!
        if (count($fixturesList) > 0) {
            $this->fixturesExecutor->execute(
                $this->objectManager,
                $this->loaderGenerator->generate(
                    $this->loader,
                    $this->fixturesLoader,
                    $this->kernel->getBundles(),
                    $this->kernel->getEnvironment()
                ),
                $fixturesList,
                $append,
                function () {},
                false
            );
        }
    }
}

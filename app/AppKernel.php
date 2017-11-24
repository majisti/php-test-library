<?php

use PSS\SymfonyMockerContainer\DependencyInjection\MockerContainer;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Hautelook\AliceBundle\HautelookAliceBundle(),
            new AcmeBundle\AcmeBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'), true)) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle();
        }

        if (in_array($this->getEnvironment(), array('test'), true)) {
            $bundles[] = new Majisti\Testing\Bridge\Symfony\TestingBundle\MajistiTestingBundle();
        }

        return $bundles;
    }

    public function getRootDir()
    {
        return __DIR__;
    }
    public function getCacheDir()
    {
        return dirname(__DIR__).'/var/cache/'.$this->getEnvironment();
    }

    public function getLogDir()
    {
        return dirname(__DIR__).'/var/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }

    protected function getContainerBaseClass()
    {
        if ('test' === $this->getEnvironment()) {
            return MockerContainer::class;
        }

        return parent::getContainerBaseClass();
    }
}

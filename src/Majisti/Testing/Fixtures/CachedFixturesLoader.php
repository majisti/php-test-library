<?php

namespace Majisti\Testing\Fixtures;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;

class CachedFixturesLoader implements FixturesLoader
{
    /**
     * @var FixturesLoader[]
     */
    private $fixturesLoaders;

    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(array $fixturesLoaders, EntityManager $entityManager, $cacheDir)
    {
        $this->fixturesLoaders = $fixturesLoaders;
        $this->cacheDir = $cacheDir;
        $this->entityManager = $entityManager;
    }

    public function getFixturesList()
    {
        $fixturesToLoad = [];
        foreach ($this->fixturesLoaders as $fixturesLoader) {
            $fixturesToLoad = array_merge($fixturesToLoad, $fixturesLoader->getFixturesList());
        }

        return $fixturesToLoad;
    }

    public function load($append = false)
    {
        $fixturesToLoad = $this->getFixturesList();
        sort($fixturesToLoad);

        $parameters = $this->entityManager->getConnection()->getParams();
        $database = isset($parameters['path']) ? $parameters['path'] : $parameters['dbname'];
        $backupDatabase = sprintf('%s/test_populated_%s.db', $this->cacheDir, md5(serialize($fixturesToLoad)));

        if (file_exists($backupDatabase)) {
            copy($backupDatabase, $database);
        } else {
            $schemaLoader = new CachedSchemaLoader($this->entityManager, $this->cacheDir);
            $schemaLoader->load(ORMPurger::PURGE_MODE_DELETE); //todo: parametrize purge mode?

            foreach ($this->fixturesLoaders as $fixturesLoader) {
                $fixturesLoader->load($append);
                $append = true;
            }

            copy($database, $backupDatabase);
        }
    }
}
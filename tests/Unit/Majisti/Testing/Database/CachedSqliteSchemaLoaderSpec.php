<?php

declare(strict_types=1);

namespace Unit\Majisti\Testing\Database;

use Doctrine\ORM\Tools\SchemaTool;
use Majisti\Testing\Database\PersistenceProvider;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;

class CachedSqliteSchemaLoaderSpec extends ObjectBehavior
{
    public function let(PersistenceProvider $persistenceProvider, Filesystem $filesystem, SchemaTool $schemaTool): void
    {
        $persistenceProvider->getConnectionParameters()->willReturn(['path' => 'db.sqlite', 'dbname' => 'dbName']);
        $persistenceProvider->isCurrentDriverSqlite()->willReturn(true);
        $persistenceProvider->getMetadata()->willReturn([]);
        $persistenceProvider->createSchemaTool()->willReturn($schemaTool);

        $this->beConstructedWith($persistenceProvider, $filesystem, 'a_cache_dir');
    }

    public function it_should_throw_exception_on_non_sqlite_driver(PersistenceProvider $persistenceProvider): void
    {
        $persistenceProvider->isCurrentDriverSqlite()->willReturn(false);
        $this->shouldThrow(\LogicException::class)->during('load');
    }

    public function it_should_load_and_cache_sqlite_schema(Filesystem $filesystem, SchemaTool $schemaTool): void
    {
        $filesystem->exists(Argument::any())->willReturn(false);
        $schemaTool->dropDatabase()->shouldBeCalled();
        $schemaTool->createSchema([])->shouldBeCalled();

        $filesystem->copy(
            Argument::containingString('db.sqlite'),
            Argument::containingString('a_cache_dir/dbName_schema_'),
            Argument::is(true)
        )->shouldBeCalled();

        $this->load();
    }

    public function it_should_take_cached_database_when_already_cached(Filesystem $filesystem,
        SchemaTool $schemaTool
    ): void {
        $filesystem->exists(Argument::any())->willReturn(true);
        $schemaTool->dropDatabase()->shouldNotBeCalled();

        $filesystem->copy(
            Argument::containingString('a_cache_dir/dbName_schema_'),
            Argument::containingString('db.sqlite'),
            Argument::is(true)
        )->shouldBeCalled();

        $this->load();
    }
}

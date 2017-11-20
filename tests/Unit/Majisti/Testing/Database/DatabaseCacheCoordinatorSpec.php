<?php

declare(strict_types=1);

namespace Unit\Majisti\Testing\Database;

use Majisti\Testing\Database\CachedSqliteSchemaLoader;
use Majisti\Testing\Fixtures\FixturesLoader;
use Majisti\Testing\Database\SqliteDatabaseBackupTool;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DatabaseCacheCoordinatorSpec extends ObjectBehavior
{
    public function let(FixturesLoader $fixturesLoader, CachedSqliteSchemaLoader $schemaLoader,
        SqliteDatabaseBackupTool $backupTool
    ): void {
        $backupTool->setBackupDirectory(Argument::any())->willReturn($backupTool);

        $this->beConstructedWith($fixturesLoader, $schemaLoader, $backupTool, 'a_cache_dir', 'a_logs_dir');
    }

    public function it_should_load_fixtures_and_then_backup_database_for_the_first_time(SqliteDatabaseBackupTool $backupTool,
        FixturesLoader $fixturesLoader
    ): void {
        $backupTool->isAlreadyBackedUp('populated')->willReturn(false);
        $backupTool->backupDatabase('populated')->shouldBeCalled();
        $fixturesLoader->loadFullFixtures()->shouldBeCalled();

        $this->loadFullData();
    }

    public function it_should_load_cached_database_on_second_time_without_loading_fixtures(SqliteDatabaseBackupTool $backupTool,
        FixturesLoader $fixturesLoader
    ): void {
        $backupTool->isAlreadyBackedUp('populated')->willReturn(true);
        $backupTool->backupDatabase('populated')->shouldNotBeCalled();
        $backupTool->restoreDatabase('populated')->shouldBeCalled();
        $fixturesLoader->loadFullFixtures()->shouldNotBeCalled();

        $this->loadFullData();
    }

    public function it_can_create_cached_schema(CachedSqliteSchemaLoader $schemaLoader): void
    {
        $schemaLoader->load()->shouldBeCalled();
        $this->createCachedSchema();
    }

    public function it_can_snapshot_database(SqliteDatabaseBackupTool $backupTool): void
    {
        $backupTool->setBackupDirectory('a_logs_dir')->shouldBeCalled()->willReturn($backupTool);
        $backupTool->backupDatabase('a_name')->shouldBeCalled();
        $backupTool->backupDatabase('snapshot')->shouldBeCalled();

        $this->snapshotDatabaseWithName('a_name');
        $this->snapshotDatabase();
    }
}

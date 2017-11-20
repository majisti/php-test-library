<?php

declare(strict_types=1);

namespace Unit\Majisti\Testing\Database;

use Doctrine\DBAL\Connection as DriverConnection;
use Doctrine\ORM\EntityManagerInterface;
use Majisti\Testing\Filesystem\FriendlyPathBuilder;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;

class SqliteDatabaseBackupToolSpec extends ObjectBehavior
{
    public function let(
        EntityManagerInterface $entityManager,
        DriverConnection $driverConnection,
        Filesystem $filesystem,
        FriendlyPathBuilder $friendlyPathBuilder
    ): void {
        $entityManager->getConnection()->willReturn($driverConnection);
        $driverConnection->getParams()->willReturn(
            [
                'path' => '/tmp/db.sqlite',
                'dbname' => 'dbName',
            ]
        );

        $this->beConstructedWith($entityManager, $filesystem, 'a_backup_dir');

        $friendlyPathBuilder
            ->buildDefaultFriendlyPath(Argument::any(), Argument::any())
            ->willReturn($friendlyPathBuilder);
        $friendlyPathBuilder->getPath()->willReturn('');
        $this->setFriendlyPathBuilder($friendlyPathBuilder);
    }

    public function it_should_create_a_backup_of_the_current_database(
        Filesystem $filesystem,
        FriendlyPathBuilder $friendlyPathBuilder
    ): void {
        $expectedDatabasePath = 'a_backup_dir/dbName_foo.sqlite';
        $friendlyPathBuilder->buildDefaultFriendlyPath(Argument::containingString($expectedDatabasePath))->shouldBeCalled();
        $friendlyPathBuilder->getPath()->willReturn($expectedDatabasePath);

        $filesystem->copy(Argument::any(), Argument::any())->shouldBeCalled();
        $filesystem->copy(
            Argument::any(),
            Argument::containingString($expectedDatabasePath)
        )->shouldBeCalled();
        $filesystem->remove(Argument::any())->shouldBeCalled();

        $this->backupDatabase('foo');
    }

    public function it_should_not_handle_transactions_when_doing_backups_by_default(
        Filesystem $filesystem,
        EntityManagerInterface $entityManager
    ): void {
        $entityManager->commit()->shouldNotBeCalled();
        $entityManager->rollback()->shouldNotBeCalled();
        $entityManager->beginTransaction()->shouldNotBeCalled();

        $filesystem->copy(Argument::any(), Argument::any())->shouldBeCalled();
        $filesystem->remove(Argument::any())->shouldBeCalled();

        $this->backupDatabase('foo');
    }

    public function it_should_handle_transactions_when_doing_backups(
        EntityManagerInterface $entityManager,
        DriverConnection $driverConnection,
        Filesystem $filesystem
    ): void {
        $this->setShouldHandleTransactions(true);

        $driverConnection->isRollbackOnly()->willReturn(false);
        $driverConnection->isTransactionActive()->willReturn(true);

        $filesystem->copy(Argument::any(), Argument::any())->shouldBeCalled();
        $filesystem->remove(Argument::any())->shouldBeCalled();

        $entityManager->commit()->shouldBeCalled();
        $entityManager->beginTransaction()->shouldBeCalled();

        $this->backupDatabase('foo');
    }

    public function it_should_not_backup_if_transaction_is_rollback_only(
        DriverConnection $driverConnection,
        Filesystem $filesystem,
        EntityManagerInterface $entityManager
    ): void {
        $this->setShouldHandleTransactions(true);
        $driverConnection->isTransactionActive()->willReturn(true);
        $driverConnection->isRollbackOnly()->willReturn(true);

        $entityManager->commit()->shouldNotBeCalled();
        $filesystem->copy(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->backupDatabase('foo');
    }

    public function it_should_tell_if_backup_already_done(Filesystem $filesystem): void
    {
        $filesystem->exists(Argument::containingString('a_backup_dir/dbName_foo.sqlite'))->willReturn(true);

        $this->isAlreadyBackedUp('foo')->shouldBe(true);
    }

    public function it_can_restore_from_backup(Filesystem $filesystem): void
    {
        $filesystem->copy(
            Argument::containingString('a_backup_dir/dbName_backup.sqlite'),
            Argument::containingString('db.sqlite'),
            Argument::is(true)
        )->shouldBeCalled();

        $this->restoreDatabase('backup');
    }
}

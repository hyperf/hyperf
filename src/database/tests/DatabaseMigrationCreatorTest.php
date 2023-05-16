<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace HyperfTest\Database;

use Hyperf\Database\Migrations\MigrationCreator;
use Hyperf\Support\Filesystem\Filesystem;
use HyperfTest\Database\Stubs\MigrationCreatorFakeMigration;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class DatabaseMigrationCreatorTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testBasicCreateMethodStoresMigrationFile()
    {
        $creator = $this->getCreator();

        $creator->expects($this->any())->method('getDatePrefix')->will($this->returnValue('foo'));
        $creator->getFilesystem()->shouldReceive('get')->once()->with($creator->stubPath() . '/blank.stub')->andReturn('DummyClass');
        $creator->getFilesystem()->shouldReceive('put')->once()->with('foo/foo_create_bar.php', 'CreateBar');
        $creator->getFilesystem()->shouldReceive('glob')->once()->with('foo/*.php')->andReturn(['foo/foo_create_bar.php']);
        $creator->getFilesystem()->shouldReceive('requireOnce')->once()->with('foo/foo_create_bar.php');

        $creator->create('create_bar', 'foo');
    }

    public function testBasicCreateMethodCallsPostCreateHooks()
    {
        $table = 'baz';

        $creator = $this->getCreator();
        unset($_SERVER['__migration.creator']);
        $creator->afterCreate(function ($table) {
            $_SERVER['__migration.creator'] = $table;
        });

        $creator->expects($this->any())->method('getDatePrefix')->will($this->returnValue('foo'));
        $creator->getFilesystem()->shouldReceive('get')->once()->with($creator->stubPath() . '/update.stub')->andReturn('DummyClass DummyTable');
        $creator->getFilesystem()->shouldReceive('put')->once()->with('foo/foo_create_bar.php', 'CreateBar baz');
        $creator->getFilesystem()->shouldReceive('glob')->once()->with('foo/*.php')->andReturn(['foo/foo_create_bar.php']);
        $creator->getFilesystem()->shouldReceive('requireOnce')->once()->with('foo/foo_create_bar.php');

        $creator->create('create_bar', 'foo', $table);

        $this->assertEquals($_SERVER['__migration.creator'], $table);

        unset($_SERVER['__migration.creator']);
    }

    public function testTableUpdateMigrationStoresMigrationFile()
    {
        $creator = $this->getCreator();
        $creator->expects($this->any())->method('getDatePrefix')->will($this->returnValue('foo'));
        $creator->getFilesystem()->shouldReceive('get')->once()->with($creator->stubPath() . '/update.stub')->andReturn('DummyClass DummyTable');
        $creator->getFilesystem()->shouldReceive('put')->once()->with('foo/foo_create_bar.php', 'CreateBar baz');
        $creator->getFilesystem()->shouldReceive('glob')->once()->with('foo/*.php')->andReturn(['foo/foo_create_bar.php']);
        $creator->getFilesystem()->shouldReceive('requireOnce')->once()->with('foo/foo_create_bar.php');

        $creator->create('create_bar', 'foo', 'baz');
    }

    public function testTableCreationMigrationStoresMigrationFile()
    {
        $creator = $this->getCreator();
        $creator->expects($this->any())->method('getDatePrefix')->will($this->returnValue('foo'));
        $creator->getFilesystem()->shouldReceive('get')->once()->with($creator->stubPath() . '/create.stub')->andReturn('DummyClass DummyTable');
        $creator->getFilesystem()->shouldReceive('put')->once()->with('foo/foo_create_bar.php', 'CreateBar baz');
        $creator->getFilesystem()->shouldReceive('glob')->once()->with('foo/*.php')->andReturn(['foo/foo_create_bar.php']);
        $creator->getFilesystem()->shouldReceive('requireOnce')->once()->with('foo/foo_create_bar.php');

        $creator->create('create_bar', 'foo', 'baz', true);
    }

    public function testTableUpdateMigrationWontCreateDuplicateClass()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A HyperfTest\Database\Stubs\MigrationCreatorFakeMigration class already exists.');

        $creator = $this->getCreator();
        $creator->getFilesystem()->shouldReceive('get');
        $creator->getFilesystem()->shouldReceive('glob')->once()->with('foo/*.php')->andReturn(['foo/foo_create_bar.php']);
        $creator->getFilesystem()->shouldReceive('requireOnce')->once()->with('foo/foo_create_bar.php');

        $creator->create(MigrationCreatorFakeMigration::class, 'foo');
    }

    protected function getCreator()
    {
        $files = Mockery::mock(Filesystem::class);
        return $this->getMockBuilder(MigrationCreator::class)->onlyMethods(['getDatePrefix'])->setConstructorArgs([$files])->getMock();
    }
}

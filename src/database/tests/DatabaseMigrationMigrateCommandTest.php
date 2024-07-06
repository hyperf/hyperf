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

use Hyperf\Database\Commands\Migrations\MigrateCommand;
use Hyperf\Database\Migrations\Migrator;
use HyperfTest\Database\Stubs\ContainerStub;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class DatabaseMigrationMigrateCommandTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    protected function setUp(): void
    {
        parent::setUp();
        ! defined('BASE_PATH') && define('BASE_PATH', __DIR__);

        ContainerStub::unsetContainer();
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testBasicMigrationsCallMigratorWithProperArguments()
    {
        $command = new MigrateCommand($migrator = Mockery::mock(Migrator::class));
        $migrator->shouldReceive('paths')->once()->andReturn([]);
        $migrator->shouldReceive('setConnection')->once()->with('default');
        $migrator->shouldReceive('setOutput')->once()->andReturn($migrator);
        $migrator->shouldReceive('run')->once()->with([BASE_PATH . DIRECTORY_SEPARATOR . 'migrations'], ['pretend' => false, 'step' => false]);
        $migrator->shouldReceive('getNotes')->andReturn([]);
        $migrator->shouldReceive('repositoryExists')->once()->andReturn(true);

        $this->runCommand($command);
    }

    public function testMigrationRepositoryCreatedWhenNecessary()
    {
        $params = [$migrator = Mockery::mock(Migrator::class)];
        $command = $this->getMockBuilder(MigrateCommand::class)->onlyMethods(['call'])->setConstructorArgs($params)->getMock();
        $migrator->shouldReceive('paths')->once()->andReturn([]);
        $migrator->shouldReceive('setConnection')->once()->with('default');
        $migrator->shouldReceive('setOutput')->once()->andReturn($migrator);
        $migrator->shouldReceive('run')->once()->with([BASE_PATH . DIRECTORY_SEPARATOR . 'migrations'], ['pretend' => false, 'step' => false]);
        $migrator->shouldReceive('repositoryExists')->once()->andReturn(false);
        $command->expects($this->once())->method('call')->with($this->equalTo('migrate:install'), $this->equalTo([]));

        $this->runCommand($command);
    }

    public function testTheCommandMayBePretended()
    {
        $command = new MigrateCommand($migrator = Mockery::mock(Migrator::class));
        $migrator->shouldReceive('paths')->once()->andReturn([]);
        $migrator->shouldReceive('setConnection')->once()->with('default');
        $migrator->shouldReceive('setOutput')->once()->andReturn($migrator);
        $migrator->shouldReceive('run')->once()->with([BASE_PATH . DIRECTORY_SEPARATOR . 'migrations'], ['pretend' => true, 'step' => false]);
        $migrator->shouldReceive('repositoryExists')->once()->andReturn(true);

        $this->runCommand($command, ['--pretend' => true]);
    }

    public function testTheDatabaseMayBeSet()
    {
        $command = new MigrateCommand($migrator = Mockery::mock(Migrator::class));
        $migrator->shouldReceive('paths')->once()->andReturn([]);
        $migrator->shouldReceive('setConnection')->once()->with('foo');
        $migrator->shouldReceive('setOutput')->once()->andReturn($migrator);
        $migrator->shouldReceive('run')->once()->with([BASE_PATH . DIRECTORY_SEPARATOR . 'migrations'], ['pretend' => false, 'step' => false]);
        $migrator->shouldReceive('repositoryExists')->once()->andReturn(true);

        $this->runCommand($command, ['--database' => 'foo']);
    }

    public function testStepMayBeSet()
    {
        $command = new MigrateCommand($migrator = Mockery::mock(Migrator::class));
        $migrator->shouldReceive('paths')->once()->andReturn([]);
        $migrator->shouldReceive('setConnection')->once()->with('default');
        $migrator->shouldReceive('setOutput')->once()->andReturn($migrator);
        $migrator->shouldReceive('run')->once()->with([BASE_PATH . DIRECTORY_SEPARATOR . 'migrations'], ['pretend' => false, 'step' => true]);
        $migrator->shouldReceive('repositoryExists')->once()->andReturn(true);

        $this->runCommand($command, ['--step' => true]);
    }

    protected function runCommand($command, $input = [])
    {
        return $command->run(new ArrayInput($input), new NullOutput());
    }
}

class ApplicationDatabaseMigrationStub
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(array $data = [])
    {
        $this->container = Mockery::mock(ContainerInterface::class);
        foreach ($data as $abstract => $instance) {
            $this->container->shouldReceive('get')->with($abstract)->andReturn(class_exists($instance) ? new $instance() : $instance);
        }
    }

    public function environment(...$environments)
    {
        return 'development';
    }
}

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

use Hyperf\Database\Commands\Migrations\ResetCommand;
use Hyperf\Database\Migrations\Migrator;
use HyperfTest\Database\Stubs\ContainerStub;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class DatabaseMigrationResetCommandTest extends TestCase
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

    public function testResetCommandCallsMigratorWithProperArguments()
    {
        $command = new ResetCommand($migrator = Mockery::mock(Migrator::class));
        $migrator->shouldReceive('paths')->once()->andReturn([]);
        $migrator->shouldReceive('setConnection')->once()->with('default');
        $migrator->shouldReceive('repositoryExists')->once()->andReturn(true);
        $migrator->shouldReceive('setOutput')->once()->andReturn($migrator);
        $migrator->shouldReceive('reset')->once()->with([BASE_PATH . DIRECTORY_SEPARATOR . 'migrations'], false);

        $this->runCommand($command);
    }

    public function testResetCommandCanBePretended()
    {
        $command = new ResetCommand($migrator = Mockery::mock(Migrator::class));
        $migrator->shouldReceive('paths')->once()->andReturn([]);
        $migrator->shouldReceive('setConnection')->once()->with('foo');
        $migrator->shouldReceive('repositoryExists')->once()->andReturn(true);
        $migrator->shouldReceive('setOutput')->once()->andReturn($migrator);
        $migrator->shouldReceive('reset')->once()->with([BASE_PATH . DIRECTORY_SEPARATOR . 'migrations'], true);

        $this->runCommand($command, ['--pretend' => true, '--database' => 'foo']);
    }

    protected function runCommand($command, $input = [])
    {
        return $command->run(new ArrayInput($input), new NullOutput());
    }
}

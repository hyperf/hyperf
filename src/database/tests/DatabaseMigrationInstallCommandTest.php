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

use Hyperf\Database\Commands\Migrations\InstallCommand;
use Hyperf\Database\Migrations\MigrationRepositoryInterface;
use HyperfTest\Database\Stubs\ContainerStub;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class DatabaseMigrationInstallCommandTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected function setUp(): void
    {
        ContainerStub::unsetContainer();
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testFireCallsRepositoryToInstall()
    {
        $command = new InstallCommand($repo = Mockery::mock(MigrationRepositoryInterface::class));
        $repo->shouldReceive('setSource')->once()->with('foo');
        $repo->shouldReceive('createRepository')->once();

        $this->runCommand($command, ['--database' => 'foo']);
    }

    protected function runCommand($command, $options = [])
    {
        return $command->run(new ArrayInput($options), new NullOutput());
    }
}

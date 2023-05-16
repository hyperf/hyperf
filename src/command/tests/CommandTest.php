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
namespace HyperfTest\Command;

use Hyperf\Context\ApplicationContext;
use Hyperf\Support\Reflection\ClassInvoker;
use HyperfTest\Command\Command\DefaultSwooleFlagsCommand;
use HyperfTest\Command\Command\FooCommand;
use HyperfTest\Command\Command\FooExceptionCommand;
use HyperfTest\Command\Command\FooExitCommand;
use HyperfTest\Command\Command\SwooleFlagsCommand;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 * @coversNothing
 */
class CommandTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testHookFlags()
    {
        $command = new DefaultSwooleFlagsCommand('test:demo');
        $this->assertSame(SWOOLE_HOOK_ALL, $command->getHookFlags());

        $command = new SwooleFlagsCommand('test:demo2');
        $this->assertSame(SWOOLE_HOOK_ALL | SWOOLE_HOOK_CURL, $command->getHookFlags());
    }

    /**
     * @group NonCoroutine
     */
    public function testExitCodeWhenThrowException()
    {
        ApplicationContext::setContainer($container = Mockery::mock(ContainerInterface::class));
        $container->shouldReceive('has')->with(EventDispatcherInterface::class)->andReturnFalse();

        /** @var FooExceptionCommand $command */
        $command = new ClassInvoker(new FooExceptionCommand('foo'));
        $input = Mockery::mock(InputInterface::class);
        $input->shouldReceive('getOption')->andReturnFalse();
        $output = Mockery::mock(OutputInterface::class);
        $output->shouldReceive('writeln')->withAnyArgs()->andReturnNull();

        $exitCode = $command->execute($input, $output);
        $this->assertSame(99, $exitCode);

        /** @var FooExitCommand $command */
        $command = new ClassInvoker(new FooExitCommand());
        $exitCode = $command->execute($input, $output);
        $this->assertSame(11, $exitCode);

        /** @var FooCommand $command */
        $command = new ClassInvoker(new FooCommand());
        $exitCode = $command->execute($input, $output);
        $this->assertSame(0, $exitCode);
    }

    public function testExitCodeWhenThrowExceptionInCoroutine()
    {
        $this->testExitCodeWhenThrowException();
    }
}

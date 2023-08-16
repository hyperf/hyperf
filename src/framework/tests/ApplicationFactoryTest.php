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
namespace HyperfTest\Framework;

use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Framework\ApplicationFactory;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\Support\Reflection\ClassInvoker;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface as PsrEventDispatcherInterface;
use Symfony\Component\Console\Application;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 * @coversNothing
 */
class ApplicationFactoryTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testInvokeApplicationWithSymfonyEventDispatcher()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('has')->andReturnTrue();
        $container->shouldReceive('get')->with(PsrEventDispatcherInterface::class)->andReturn($event = Mockery::mock(PsrEventDispatcherInterface::class));
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(
            new Config([
                'symfony' => [
                    'event' => ['enable' => true],
                ],
            ])
        );
        $event->shouldReceive('dispatch')->once()->andReturnUsing(function ($boot) {
            $this->assertInstanceOf(BootApplication::class, $boot);
        });

        /** @var Application $application */
        $application = new ClassInvoker((new ApplicationFactory())($container));
        $this->assertInstanceOf(EventDispatcherInterface::class, $application->dispatcher);
    }

    public function testInvokeApplicationWithoutSymfonyEventDispatcher()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('has')->andReturnTrue();
        $container->shouldReceive('get')->with(PsrEventDispatcherInterface::class)->andReturn($event = Mockery::mock(PsrEventDispatcherInterface::class));
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config([]));
        $event->shouldReceive('dispatch')->once()->andReturnUsing(function ($boot) {
            $this->assertInstanceOf(BootApplication::class, $boot);
        });

        /** @var Application $application */
        $application = new ClassInvoker((new ApplicationFactory())($container));
        $this->assertNull($application->dispatcher);
    }
}

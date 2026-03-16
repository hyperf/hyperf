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

namespace HyperfTest\Metric\Cases;

use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Container;
use Hyperf\Metric\Adapter\Prometheus\MetricFactory as PrometheusFactory;
use Hyperf\Metric\Contract\MetricFactoryInterface;
use Hyperf\Metric\Listener\OnWorkerStart;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class OnWorkerStartTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testHandle()
    {
        $config = new Config([
            'metric' => [
                'default' => 'prometheus',
                'use_standalone_process' => false,
                'enable_default_metrics' => false,
            ],
        ]);

        $factory = Mockery::mock(PrometheusFactory::class);
        $factory->shouldReceive('handle')->atLeast()->times(1);
        $container = Mockery::mock(Container::class);
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn($config);
        $container->shouldReceive('get')->with(MetricFactoryInterface::class)->andReturn($factory);

        $l = new OnWorkerStart($container);

        $l->process(new class {
            public $workerId = 1;
        });

        $this->assertTrue(true);
    }

    public function testFireEvent()
    {
        $config = new Config([
            'metric' => [
                'default' => 'prometheus',
                'use_standalone_process' => false,
                'enable_default_metrics' => false,
            ],
        ]);
        $factory = Mockery::mock(PrometheusFactory::class);
        $container = Mockery::mock(Container::class);
        $factory->shouldReceive('handle')->atLeast()->times(1);
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn($config);
        $container->shouldReceive('get')->with(MetricFactoryInterface::class)->andReturn($factory);
        $container->shouldReceive('get')->with(EventDispatcherInterface::class)->andReturn(
            new class {
                public function dispatch()
                {
                    return true;
                }
            }
        )->once();
        $l = new OnWorkerStart($container);
        $l->process(new class {
            public $workerId = 0;
        });
        $l->process(new class {
            public $workerId = 1;
        });
        $this->assertTrue(true);
    }

    public function testNotFireEvent()
    {
        $config = new Config([
            'metric' => [
                'default' => 'prometheus',
                'use_standalone_process' => true,
                'enable_default_metrics' => false,
            ],
        ]);
        $factory = Mockery::mock(PrometheusFactory::class);
        $container = Mockery::mock(Container::class);
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn($config);
        $container->shouldReceive('get')->with(MetricFactoryInterface::class)->andReturn($factory);
        $l = new OnWorkerStart($container);
        $l->process(new class {
            public $workerId = 0;
        });
        $this->assertTrue(true);
    }
}

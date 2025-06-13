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
use Hyperf\Metric\Adapter\NoOp\MetricFactory as NoOpFactory;
use Hyperf\Metric\Adapter\Prometheus\MetricFactory as PrometheusFactory;
use Hyperf\Metric\Adapter\RemoteProxy\MetricFactory as RemoteFactory;
use Hyperf\Metric\Adapter\StatsD\MetricFactory as StatsDFactory;
use Hyperf\Metric\MetricFactoryPicker;
use Hyperf\Process\ProcessCollector;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Swoole\Process;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class MetricFactoryPickerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testPrometheus()
    {
        $config = new Config([
            'metric' => [
                'default' => 'prometheus',
                'use_standalone_process' => false,
                'enable_default_metrics' => true,
            ],
        ]);
        $container = Mockery::mock(Container::class);
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn($config);

        $container->shouldReceive('get')->with(PrometheusFactory::class)->andReturn(Mockery::mock(PrometheusFactory::class));

        $picker = new MetricFactoryPicker();

        $this->assertInstanceOf(PrometheusFactory::class, $picker($container));
    }

    public function testStatsD()
    {
        $config = new Config([
            'metric' => [
                'default' => 'statsD',
                'use_standalone_process' => false,
                'enable_default_metrics' => true,
                'metric' => [
                    'prometheus' => [
                        'driver' => PrometheusFactory::class,
                    ],
                    'statsD' => [
                        'driver' => StatsDFactory::class,
                    ],
                ],
            ],
        ]);
        $container = Mockery::mock(Container::class);
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn($config);
        $container->shouldReceive('get')->with(StatsDFactory::class)->andReturn(Mockery::mock(StatsDFactory::class));

        $picker = new MetricFactoryPicker();

        $this->assertInstanceOf(StatsDFactory::class, $picker($container));
    }

    public function testProxy()
    {
        $config = new Config([
            'metric' => [
                'default' => 'statsD',
                'use_standalone_process' => true,
                'enable_default_metrics' => true,
                'metric' => [
                    'prometheus' => [
                        'driver' => PrometheusFactory::class,
                    ],
                    'statsD' => [
                        'driver' => StatsDFactory::class,
                    ],
                ],
            ],
        ]);
        ProcessCollector::add('dummy', Mockery::mock(Process::class));
        $container = Mockery::mock(Container::class);
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn($config);
        $container->shouldReceive('get')->with(RemoteFactory::class)->andReturn(Mockery::mock(RemoteFactory::class));

        $picker = new MetricFactoryPicker();

        $this->assertInstanceOf(RemoteFactory::class, $picker($container));
    }

    public function testMetricProcess()
    {
        $config = new Config([
            'metric' => [
                'default' => 'prometheus',
                'use_standalone_process' => true,
                'enable_default_metrics' => false,
                'metric' => [
                    'prometheus' => [
                        'driver' => PrometheusFactory::class,
                    ],
                    'statsD' => [
                        'driver' => StatsDFactory::class,
                    ],
                ],
            ],
        ]);
        ProcessCollector::add('dummy', Mockery::mock(Process::class));
        $container = Mockery::mock(Container::class);
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn($config);
        $container->shouldReceive('get')->with(PrometheusFactory::class)->andReturn(Mockery::mock(PrometheusFactory::class));

        MetricFactoryPicker::$inMetricProcess = true;
        $picker = new MetricFactoryPicker();

        $this->assertInstanceOf(PrometheusFactory::class, $picker($container));
    }

    public function testNoOpDriver()
    {
        $config = new Config([
            'metric' => [
                'default' => 'noop',
                'metric' => [
                    'noop' => [
                        'driver' => NoOpFactory::class,
                    ],
                    'statsD' => [
                        'driver' => StatsDFactory::class,
                    ],
                ],
            ],
        ]);
        $container = Mockery::mock(Container::class);
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn($config);
        $container->shouldReceive('get')->with(NoOpFactory::class)->andReturn(Mockery::mock(NoOpFactory::class));

        MetricFactoryPicker::$inMetricProcess = true;
        $picker = new MetricFactoryPicker();

        $this->assertInstanceOf(NoOpFactory::class, $picker($container));
    }
}

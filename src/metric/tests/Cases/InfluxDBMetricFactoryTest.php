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
use Hyperf\Guzzle\ClientFactory;
use Hyperf\Metric\Adapter\InfluxDB\MetricFactory as InfluxDBFactory;
use Hyperf\Metric\Contract\CounterInterface;
use Hyperf\Metric\Contract\GaugeInterface;
use Hyperf\Metric\Contract\HistogramInterface;
use InfluxDB2\Point;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Prometheus\CollectorRegistry;
use Prometheus\Counter;
use Prometheus\Gauge;
use Prometheus\Histogram;
use Prometheus\Sample;
use ReflectionClass;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class InfluxDBMetricFactoryTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testMakeCounter()
    {
        $registry = Mockery::mock(CollectorRegistry::class);
        $registry->shouldReceive('getOrRegisterCounter')
            ->once()
            ->andReturn(Mockery::mock(Counter::class));

        $factory = $this->createInfluxDBFactory($registry);
        $counter = $factory->makeCounter('test_counter', ['label1', 'label2']);

        $this->assertInstanceOf(CounterInterface::class, $counter);
    }

    public function testMakeGauge()
    {
        $registry = Mockery::mock(CollectorRegistry::class);
        $registry->shouldReceive('getOrRegisterGauge')
            ->once()
            ->andReturn(Mockery::mock(Gauge::class));

        $factory = $this->createInfluxDBFactory($registry);
        $gauge = $factory->makeGauge('test_gauge', ['label1']);

        $this->assertInstanceOf(GaugeInterface::class, $gauge);
    }

    public function testMakeHistogram()
    {
        $registry = Mockery::mock(CollectorRegistry::class);
        $registry->shouldReceive('getOrRegisterHistogram')
            ->once()
            ->andReturn(Mockery::mock(Histogram::class));

        $factory = $this->createInfluxDBFactory($registry);
        $histogram = $factory->makeHistogram('test_histogram');

        $this->assertInstanceOf(HistogramInterface::class, $histogram);
    }

    public function testCreatePoint()
    {
        $factory = $this->createInfluxDBFactory();
        $reflection = new ReflectionClass($factory);
        $method = $reflection->getMethod('createPoint');

        $sample = Mockery::mock(Sample::class);
        $sample->shouldReceive('getName')->andReturn('test_metric');
        $sample->shouldReceive('getValue')->andReturn(42.5);
        $sample->shouldReceive('getLabelNames')->andReturn(['label1', 'label2']);
        $sample->shouldReceive('getLabelValues')->andReturn(['value1', 'value2']);

        $point = $method->invokeArgs($factory, [$sample]);

        $this->assertInstanceOf(Point::class, $point);
    }

    public function testGetNamespace()
    {
        $config = new Config([
            'metric' => [
                'default' => 'influxdb',
                'metric' => [
                    'influxdb' => [
                        'namespace' => 'Test-App_Name!',
                    ],
                ],
            ],
        ]);

        $factory = new InfluxDBFactory(
            $config,
            Mockery::mock(CollectorRegistry::class),
            Mockery::mock(ClientFactory::class)
        );

        $reflection = new ReflectionClass($factory);
        $method = $reflection->getMethod('getNamespace');

        $namespace = $method->invoke($factory);
        $this->assertEquals('test__app__name_', $namespace);
    }

    private function createInfluxDBFactory(?CollectorRegistry $registry = null): InfluxDBFactory
    {
        $config = new Config([
            'metric' => [
                'default' => 'influxdb',
                'metric' => [
                    'influxdb' => [
                        'namespace' => 'Test-App_Name!',
                        'host' => '127.0.0.1',
                        'port' => '8086',
                        'token' => 'test-token',
                        'bucket' => 'test-bucket',
                        'org' => 'test-org',
                        'push_interval' => 5,
                    ],
                ],
            ],
        ]);

        return new InfluxDBFactory(
            $config,
            $registry ?: Mockery::mock(CollectorRegistry::class),
            Mockery::mock(ClientFactory::class)
        );
    }
}

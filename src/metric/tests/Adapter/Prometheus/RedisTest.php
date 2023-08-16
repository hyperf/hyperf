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
namespace HyperfTest\Metric\Adapter\Prometheus;

use Hyperf\Metric\Adapter\Prometheus\Redis;
use Mockery;
use PHPUnit\Framework\TestCase;
use Prometheus\Counter;
use Prometheus\Histogram;
use ReflectionMethod;
use ReflectionProperty;

/**
 * @internal
 * @coversNothing
 */
class RedisTest extends TestCase
{
    protected string $prePrefix;

    protected string $preMetricGatherKeySuffix;

    protected function setUp(): void
    {
        parent::setUp();

        $prefixProperty = new ReflectionProperty(Redis::class, 'prefix');
        $prefixProperty->setAccessible(true);

        $metricGatherKeySuffix = new ReflectionProperty(Redis::class, 'metricGatherKeySuffix');
        $metricGatherKeySuffix->setAccessible(true);

        $this->prePrefix = $prefixProperty->getDefaultValue();
        $this->preMetricGatherKeySuffix = $metricGatherKeySuffix->getDefaultValue();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Redis::setPrefix($this->prePrefix);
        Redis::setMetricGatherKeySuffix($this->preMetricGatherKeySuffix);

        Mockery::close();
    }

    public function testToMetricKey()
    {
        $data = [
            'type' => Counter::TYPE,
            'name' => 'hyperf_metric',
        ];

        Redis::setPrefix('prometheus:');
        $method = new ReflectionMethod(Redis::class, 'toMetricKey');
        $method->setAccessible(true);
        self::assertEquals('prometheus:counter:hyperf_metric{counter}', $method->invoke(new Redis(new \Redis()), $data));

        // 兼容 < v3.1
        Redis::setPrefix('PROMETHEUS_');
        $method = new ReflectionMethod(Redis::class, 'toMetricKey');
        $method->setAccessible(true);
        self::assertEquals('PROMETHEUS_:counter:hyperf_metric{counter}', $method->invoke(new Redis(new \Redis()), $data));
    }

    public function testGetMetricGatherKey()
    {
        $method = new ReflectionMethod(Redis::class, 'getMetricGatherKey');
        $method->setAccessible(true);

        self::assertEquals('PROMETHEUS_counter_METRIC_KEYS{counter}', $method->invoke(new Redis(new \Redis()), Counter::TYPE));
    }

    public function testCollectSamples()
    {
        Redis::setPrefix('prometheus:');
        Redis::setMetricGatherKeySuffix(':metric_keys');

        $redis = Mockery::mock(\Redis::class);
        $redis->shouldReceive('sMembers')->withArgs(['prometheus:counter:metric_keys{counter}'])->times(1)->andReturn([
            'hyperf:prometheus:counter:hyperf_metric1{counter}',
            'hyperf:prometheus:counter:hyperf_metric2{counter}',
        ]);
        $redis->shouldReceive('_prefix')->times(2)->andReturn('hyperf:');
        $redis->shouldReceive('hGetAll')->with('prometheus:counter:hyperf_metric1{counter}')->times(1)->andReturn(
            [
                '__meta' => '{"type":"counter","name":"hyperf_metric1","help":"hyperf_metric1","labelNames":["class","method"]}',
                '["class_name", "method_name"]' => '1',
            ],
        );
        $redis->shouldReceive('hGetAll')->with('prometheus:counter:hyperf_metric2{counter}')->times(1)->andReturn(
            [
                '__meta' => '{"type":"counter","name":"hyperf_metric2","help":"hyperf_metric2","labelNames":["name"]}',
                '["hyperf"]' => '1',
            ],
        );

        $method = new ReflectionMethod(Redis::class, 'collectSamples');
        $method->setAccessible(true);
        $result = $method->invoke(new Redis($redis), Counter::TYPE);

        self::assertEquals([
            [
                'type' => Counter::TYPE,
                'name' => 'hyperf_metric1',
                'help' => 'hyperf_metric1',
                'labelNames' => ['class', 'method'],
                'samples' => [
                    [
                        'name' => 'hyperf_metric1',
                        'labelNames' => [],
                        'labelValues' => ['class_name', 'method_name'],
                        'value' => 1,
                    ],
                ],
            ],
            [
                'type' => Counter::TYPE,
                'name' => 'hyperf_metric2',
                'help' => 'hyperf_metric2',
                'labelNames' => ['name'],
                'samples' => [
                    [
                        'name' => 'hyperf_metric2',
                        'labelNames' => [],
                        'labelValues' => ['hyperf'],
                        'value' => 1,
                    ],
                ],
            ],
        ], $result);
    }

    public function testCollectSamplesLabelNameNotMatch()
    {
        Redis::setPrefix('prometheus:');
        Redis::setMetricGatherKeySuffix(':metric_keys');

        $redis = Mockery::mock(\Redis::class);
        $redis->shouldReceive('sMembers')->withArgs(['prometheus:counter:metric_keys{counter}'])->times(1)->andReturn([
            'hyperf:prometheus:counter:hyperf_metric1{counter}',
        ]);
        $redis->shouldReceive('_prefix')->times(1)->andReturn('hyperf:');
        $redis->shouldReceive('hGetAll')->with('prometheus:counter:hyperf_metric1{counter}')->times(1)->andReturn(
            [
                '__meta' => '{"type":"counter","name":"hyperf_metric1","help":"hyperf_metric1","labelNames":["class","method"]}',
                '["class_name"]' => '1',
            ],
        );

        $method = new ReflectionMethod(Redis::class, 'collectSamples');
        $method->setAccessible(true);
        $result = $method->invoke(new Redis($redis), Counter::TYPE);

        self::assertEquals([
            [
                'type' => Counter::TYPE,
                'name' => 'hyperf_metric1',
                'help' => 'hyperf_metric1',
                'labelNames' => ['class', 'method'],
                'samples' => [
                ],
            ],
        ], $result);
    }

    public function testCollectHistograms()
    {
        Redis::setPrefix('prometheus:');
        Redis::setMetricGatherKeySuffix(':metric_keys');

        $redis = Mockery::mock(\Redis::class);
        $redis->shouldReceive('sMembers')->withArgs(['prometheus:histogram:metric_keys{histogram}'])->times(1)->andReturn([
            'hyperf:prometheus:histogram:hyperf_metric1{histogram}',
            'hyperf:prometheus:histogram:hyperf_metric2{histogram}',
        ]);
        $redis->shouldReceive('_prefix')->times(2)->andReturn('hyperf:');
        $redis->shouldReceive('hGetAll')->with('prometheus:histogram:hyperf_metric1{histogram}')->times(1)->andReturn(
            [
                '__meta' => '{"type":"histogram","name":"hyperf_metric1","help":"hyperf_metric1","labelNames":["class","method"],"buckets":[0.1,1.0]}',
                '{"b":"sum","labelValues":["class_name","method_name"]}' => 0.55,
                '{"b":0.1,"labelValues":["class_name","method_name"]}' => 1, // 0.05
                '{"b":1,"labelValues":["class_name","method_name"]}' => 1, // 0.5
            ],
        );
        $redis->shouldReceive('hGetAll')->with('prometheus:histogram:hyperf_metric2{histogram}')->times(1)->andReturn(
            [
                '__meta' => '{"type":"histogram","name":"hyperf_metric2","help":"hyperf_metric2","labelNames":["class2","method2"],"buckets":[0.1,1.0]}',
                '{"b":"sum","labelValues":["class_name2","method_name2"]}' => 0.55,
                '{"b":0.1,"labelValues":["class_name2","method_name2"]}' => 1, // 0.05
                '{"b":1,"labelValues":["class_name2","method_name2"]}' => 1, // 0.5
            ],
        );

        $method = new ReflectionMethod(Redis::class, 'collectHistograms');
        $method->setAccessible(true);
        $result = $method->invoke(new Redis($redis), Histogram::TYPE);

        self::assertEquals([
            [
                'type' => Histogram::TYPE,
                'name' => 'hyperf_metric1',
                'help' => 'hyperf_metric1',
                'labelNames' => ['class', 'method'],
                'samples' => [
                    [
                        'name' => 'hyperf_metric1_bucket',
                        'labelNames' => ['le'],
                        'labelValues' => ['class_name', 'method_name', 0.1],
                        'value' => 1,
                    ],
                    [
                        'name' => 'hyperf_metric1_bucket',
                        'labelNames' => ['le'],
                        'labelValues' => ['class_name', 'method_name', 1.0],
                        'value' => 2,
                    ],
                    [
                        'name' => 'hyperf_metric1_bucket',
                        'labelNames' => ['le'],
                        'labelValues' => ['class_name', 'method_name', '+Inf'],
                        'value' => 2,
                    ],
                    [
                        'name' => 'hyperf_metric1_count',
                        'labelNames' => [],
                        'labelValues' => ['class_name', 'method_name'],
                        'value' => 2,
                    ],
                    [
                        'name' => 'hyperf_metric1_sum',
                        'labelNames' => [],
                        'labelValues' => ['class_name', 'method_name'],
                        'value' => 0.55,
                    ],
                ],
                'buckets' => [0.1, 1.0, '+Inf'],
            ],
            [
                'type' => Histogram::TYPE,
                'name' => 'hyperf_metric2',
                'help' => 'hyperf_metric2',
                'labelNames' => ['class2', 'method2'],
                'samples' => [
                    [
                        'name' => 'hyperf_metric2_bucket',
                        'labelNames' => ['le'],
                        'labelValues' => ['class_name2', 'method_name2', 0.1],
                        'value' => 1,
                    ],
                    [
                        'name' => 'hyperf_metric2_bucket',
                        'labelNames' => ['le'],
                        'labelValues' => ['class_name2', 'method_name2', 1.0],
                        'value' => 2,
                    ],
                    [
                        'name' => 'hyperf_metric2_bucket',
                        'labelNames' => ['le'],
                        'labelValues' => ['class_name2', 'method_name2', '+Inf'],
                        'value' => 2,
                    ],
                    [
                        'name' => 'hyperf_metric2_count',
                        'labelNames' => [],
                        'labelValues' => ['class_name2', 'method_name2'],
                        'value' => 2,
                    ],
                    [
                        'name' => 'hyperf_metric2_sum',
                        'labelNames' => [],
                        'labelValues' => ['class_name2', 'method_name2'],
                        'value' => 0.55,
                    ],
                ],
                'buckets' => [0.1, 1.0, '+Inf'],
            ],
        ], $result);
    }

    public function testCollectHistogramsLabelNotMatch()
    {
        Redis::setPrefix('prometheus:');
        Redis::setMetricGatherKeySuffix(':metric_keys');

        $redis = Mockery::mock(\Redis::class);
        $redis->shouldReceive('sMembers')->withArgs(['prometheus:histogram:metric_keys{histogram}'])->times(1)->andReturn([
            'hyperf:prometheus:histogram:hyperf_metric1{histogram}',
        ]);
        $redis->shouldReceive('_prefix')->times(1)->andReturn('hyperf:');
        $redis->shouldReceive('hGetAll')->with('prometheus:histogram:hyperf_metric1{histogram}')->times(1)->andReturn(
            [
                '__meta' => '{"type":"histogram","name":"hyperf_metric1","help":"hyperf_metric1","labelNames":["class","method"],"buckets":[0.1,1.0]}',
                '{"b":"sum","labelValues":["class_name","method_name"]}' => 0.55,
                '{"b":0.1,"labelValues":["class_name","method_name"]}' => 1, // 0.05
                '{"b":1,"labelValues":["class_name","method_name", "foo"]}' => 1, // 0.5
            ],
        );

        $method = new ReflectionMethod(Redis::class, 'collectHistograms');
        $method->setAccessible(true);
        $result = $method->invoke(new Redis($redis), Histogram::TYPE);

        self::assertEquals([
            [
                'type' => Histogram::TYPE,
                'name' => 'hyperf_metric1',
                'help' => 'hyperf_metric1',
                'labelNames' => ['class', 'method'],
                'samples' => [
                    [
                        'name' => 'hyperf_metric1_bucket',
                        'labelNames' => ['le'],
                        'labelValues' => ['class_name', 'method_name', 0.1],
                        'value' => 1,
                    ],
                    [
                        'name' => 'hyperf_metric1_bucket',
                        'labelNames' => ['le'],
                        'labelValues' => ['class_name', 'method_name', 1.0],
                        'value' => 1,
                    ],
                    [
                        'name' => 'hyperf_metric1_bucket',
                        'labelNames' => ['le'],
                        'labelValues' => ['class_name', 'method_name', '+Inf'],
                        'value' => 1,
                    ],
                    [
                        'name' => 'hyperf_metric1_count',
                        'labelNames' => [],
                        'labelValues' => ['class_name', 'method_name'],
                        'value' => 1,
                    ],
                    [
                        'name' => 'hyperf_metric1_sum',
                        'labelNames' => [],
                        'labelValues' => ['class_name', 'method_name'],
                        'value' => 0.55,
                    ],
                ],
                'buckets' => [0.1, 1.0, '+Inf'],
            ],
        ], $result);
    }
}

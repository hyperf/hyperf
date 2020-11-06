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
use Hyperf\Metric\Adapter\Prometheus\Constants;
use Hyperf\Metric\Adapter\Prometheus\MetricFactory as PrometheusFactory;
use Hyperf\Metric\Exception\RuntimeException;
use Mockery;
use PHPUnit\Framework\TestCase;
use Prometheus\CollectorRegistry;
use ReflectionMethod;

/**
 * @internal
 * @coversNothing
 */
class MetricFactoryTest extends TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    public function testPrometheusThrows()
    {
        $config = new Config([
            'metric' => [
                'default' => 'prometheus',
                'use_standalone_process' => false,
                'metric' => [
                    'prometheus' => [
                        'driver' => PrometheusFactory::class,
                        'mode' => Constants::SCRAPE_MODE,
                    ],
                ],
            ],
        ]);
        $r = Mockery::mock(CollectorRegistry::class);
        $c = Mockery::mock(ClientFactory::class);
        $this->expectException(RuntimeException::class);
        $p = new PrometheusFactory($config, $r, $c);
    }

    public function testGetNamespace()
    {
        $config = new Config([
            'metric' => [
                'default' => 'prometheus',
                'use_standalone_process' => true,
                'metric' => [
                    'prometheus' => [
                        'driver' => PrometheusFactory::class,
                        'mode' => Constants::SCRAPE_MODE,
                        'namespace' => 'Hello-World!',
                    ],
                ],
            ],
        ]);
        $r = Mockery::mock(CollectorRegistry::class);
        $c = Mockery::mock(ClientFactory::class);
        $p = new PrometheusFactory($config, $r, $c);
        $method = new ReflectionMethod(PrometheusFactory::class, 'getNamespace');
        $method->setAccessible(true);
        $this->assertEquals('hello__world_', $method->invoke($p));
    }
}

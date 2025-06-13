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

namespace HyperfTest\Tracer;

use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Container;
use Hyperf\Tracer\Adapter\JaegerTracerFactory;
use Hyperf\Tracer\Adapter\NoOpTracerFactory;
use Hyperf\Tracer\Adapter\Reporter\HttpClientFactory;
use Hyperf\Tracer\Adapter\Reporter\ReporterFactory;
use Hyperf\Tracer\Adapter\ZipkinTracerFactory;
use Hyperf\Tracer\TracerFactory;
use Mockery;
use OpenTracing\NoopTracer;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Zipkin\Reporters\Http;
use Zipkin\Samplers\BinarySampler;
use ZipkinOpenTracing\Tracer;

use function Hyperf\Support\env;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class TracerFactoryTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testOldSetting()
    {
        $config = new Config([
            'opentracing' => [
                'zipkin' => [
                    'app' => [
                        'name' => env('APP_NAME', 'skeleton'),
                        // Hyperf will detect the system info automatically as the value if ipv4, ipv6, port is null
                        'ipv4' => '127.0.0.1',
                        'ipv6' => null,
                        'port' => 9501,
                    ],
                    'options' => [
                        'endpoint_url' => env('ZIPKIN_ENDPOINT_URL', 'http://localhost:9411/api/v2/spans'),
                        'timeout' => env('ZIPKIN_TIMEOUT', 1),
                    ],
                    'sampler' => BinarySampler::createAsAlwaysSample(),
                ],
            ],
        ]);
        $container = $this->getContainer($config);
        $factory = new TracerFactory();

        $this->assertInstanceOf(Tracer::class, $factory($container));
    }

    public function testZipkinFactory()
    {
        $config = new Config([
            'opentracing' => [
                'default' => 'zipkin',
                'enable' => [
                ],
                'tracer' => [
                    'zipkin' => [
                        'driver' => ZipkinTracerFactory::class,
                        'app' => [
                            'name' => 'skeleton',
                            // Hyperf will detect the system info automatically as the value if ipv4, ipv6, port is null
                            'ipv4' => '127.0.0.1',
                            'ipv6' => null,
                            'port' => 9501,
                        ],
                        'options' => [
                        ],
                        'sampler' => BinarySampler::createAsAlwaysSample(),
                    ],
                    'jaeger' => [
                        'driver' => JaegerTracerFactory::class,
                        'name' => 'skeleton',
                        'options' => [
                        ],
                    ],
                    'noop' => [
                        'driver' => NoOpTracerFactory::class,
                    ],
                ],
            ],
        ]);
        $container = $this->getContainer($config);
        $factory = new TracerFactory();

        $this->assertInstanceOf(Tracer::class, $factory($container));
    }

    public function testJaegerFactory()
    {
        $config = new Config([
            'opentracing' => [
                'default' => 'jaeger',
                'enable' => [
                ],
                'tracer' => [
                    'zipkin' => [
                        'driver' => ZipkinTracerFactory::class,
                        'app' => [
                            'name' => 'skeleton',
                            // Hyperf will detect the system info automatically as the value if ipv4, ipv6, port is null
                            'ipv4' => '127.0.0.1',
                            'ipv6' => null,
                            'port' => 9501,
                        ],
                        'options' => [
                        ],
                        'sampler' => BinarySampler::createAsAlwaysSample(),
                    ],
                    'jaeger' => [
                        'driver' => JaegerTracerFactory::class,
                        'name' => 'skeleton',
                        'options' => [
                        ],
                    ],
                    'noop' => [
                        'driver' => NoOpTracerFactory::class,
                    ],
                ],
            ],
        ]);
        $container = $this->getContainer($config);
        $factory = new TracerFactory();

        $this->assertInstanceOf(\Jaeger\Tracer::class, $factory($container));
    }

    public function testNoOpFactory()
    {
        $config = new Config([
            'opentracing' => [
                'default' => 'noop',
                'enable' => [
                ],
                'tracer' => [
                    'zipkin' => [
                        'driver' => ZipkinTracerFactory::class,
                        'app' => [
                            'name' => 'skeleton',
                            // Hyperf will detect the system info automatically as the value if ipv4, ipv6, port is null
                            'ipv4' => '127.0.0.1',
                            'ipv6' => null,
                            'port' => 9501,
                        ],
                        'options' => [
                        ],
                        'sampler' => BinarySampler::createAsAlwaysSample(),
                    ],
                    'jaeger' => [
                        'driver' => JaegerTracerFactory::class,
                        'name' => 'skeleton',
                        'options' => [
                        ],
                    ],
                    'noop' => [
                        'driver' => NoOpTracerFactory::class,
                    ],
                ],
            ],
        ]);
        $container = $this->getContainer($config);
        $factory = new TracerFactory();

        $this->assertInstanceOf(NoopTracer::class, $factory($container));
    }

    protected function getContainer($config)
    {
        $container = Mockery::mock(Container::class);
        $client = Mockery::mock(HttpClientFactory::class);
        $reporter = Mockery::mock(ReporterFactory::class);
        $reporter->shouldReceive('make')
            ->andReturn(new Http([], $client));

        $container->shouldReceive('get')
            ->with(ZipkinTracerFactory::class)
            ->andReturn(new ZipkinTracerFactory($config, $reporter));
        $container->shouldReceive('get')
            ->with(JaegerTracerFactory::class)
            ->andReturn(new JaegerTracerFactory($config));
        $container->shouldReceive('get')
            ->with(NoOpTracerFactory::class)
            ->andReturn(new NoOpTracerFactory());
        $container->shouldReceive('get')
            ->with(ConfigInterface::class)
            ->andReturn($config);

        ApplicationContext::setContainer($container);

        return $container;
    }
}

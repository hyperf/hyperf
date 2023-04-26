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
use Hyperf\Di\Container;
use Hyperf\Tracer\TracerFactory;
use Mockery;
use PHPUnit\Framework\TestCase;
use Zipkin\Samplers\BinarySampler;

use function Hyperf\Support\env;

/**
 * @internal
 * @coversNothing
 */
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

        $this->assertInstanceOf(\ZipkinOpenTracing\Tracer::class, $factory($container));
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
                        'driver' => \Hyperf\Tracer\Adapter\ZipkinTracerFactory::class,
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
                        'driver' => \Hyperf\Tracer\Adapter\JaegerTracerFactory::class,
                        'name' => 'skeleton',
                        'options' => [
                        ],
                    ],
                ],
            ],
        ]);
        $container = $this->getContainer($config);
        $factory = new TracerFactory();

        $this->assertInstanceOf(\ZipkinOpenTracing\Tracer::class, $factory($container));
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
                        'driver' => \Hyperf\Tracer\Adapter\ZipkinTracerFactory::class,
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
                        'driver' => \Hyperf\Tracer\Adapter\JaegerTracerFactory::class,
                        'name' => 'skeleton',
                        'options' => [
                        ],
                    ],
                ],
            ],
        ]);
        $container = $this->getContainer($config);
        $factory = new TracerFactory();

        $this->assertInstanceOf(\Jaeger\Tracer::class, $factory($container));
    }

    protected function getContainer($config)
    {
        $container = Mockery::mock(Container::class);
        $client = Mockery::mock(\Hyperf\Tracer\Adapter\HttpClientFactory::class);

        $container->shouldReceive('get')
            ->with(\Hyperf\Tracer\Adapter\ZipkinTracerFactory::class)
            ->andReturn(new \Hyperf\Tracer\Adapter\ZipkinTracerFactory($config, $client));
        $container->shouldReceive('get')
            ->with(\Hyperf\Tracer\Adapter\JaegerTracerFactory::class)
            ->andReturn(new \Hyperf\Tracer\Adapter\JaegerTracerFactory($config));
        $container->shouldReceive('get')
            ->with(\Hyperf\Contract\ConfigInterface::class)
            ->andReturn($config);

        ApplicationContext::setContainer($container);

        return $container;
    }
}

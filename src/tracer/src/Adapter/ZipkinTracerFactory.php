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
namespace Hyperf\Tracer\Adapter;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Tracer\Adapter\Reporter\Kafka;
use Hyperf\Tracer\Contract\NamedFactoryInterface;
use RuntimeException;
use Zipkin\Endpoint;
use Zipkin\Reporter;
use Zipkin\Reporters\Http;
use Zipkin\Reporters\Noop;
use Zipkin\Samplers\BinarySampler;
use Zipkin\TracingBuilder;
use ZipkinOpenTracing\Tracer;

class ZipkinTracerFactory implements NamedFactoryInterface
{
    private string $prefix = 'opentracing.tracer.';

    private string $name = '';

    public function __construct(private ConfigInterface $config, private HttpClientFactory $clientFactory)
    {
    }

    public function make(string $name): \OpenTracing\Tracer
    {
        $this->name = $name;
        [$app, $options, $sampler] = $this->parseConfig();
        $endpoint = Endpoint::create($app['name'], $app['ipv4'], $app['ipv6'], $app['port']);
        $reporter = $this->getReporter($options);
        $tracing = TracingBuilder::create()
            ->havingLocalEndpoint($endpoint)
            ->havingSampler($sampler)
            ->havingReporter($reporter)
            ->build();
        return new Tracer($tracing);
    }

    private function getReporter(array $options): Reporter
    {
        return match ($options['reporter'] ?? 'http') {
            'kafka' => new Kafka(options: $options),
            'http' => new Http($options, $this->clientFactory),
            'noop' => new Noop(),
            default => throw new RuntimeException('Unsupported reporter.'),
        };
    }

    private function parseConfig(): array
    {
        // @TODO Detect the ipv4, ipv6, port from server object or system info automatically.
        return [
            $this->getConfig('app', [
                'name' => 'skeleton',
                'ipv4' => '127.0.0.1',
                'ipv6' => null,
                'port' => 9501,
            ]),
            $this->getConfig('options', [
                'timeout' => 1,
            ]),
            $this->getConfig('sampler', BinarySampler::createAsAlwaysSample()),
        ];
    }

    private function getConfig(string $key, $default)
    {
        return $this->config->get($this->getPrefix() . $key, $default);
    }

    private function getPrefix(): string
    {
        return rtrim($this->prefix . $this->name, '.') . '.';
    }
}

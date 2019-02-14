<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Tracer;

use Zipkin\Endpoint;
use Zipkin\Reporters\Http;
use Zipkin\TracingBuilder;
use Zipkin\Samplers\BinarySampler;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;

class TracingBuilderFactory
{
    /**
     * @var ConfigInterface
     */
    private $config;

    public function __invoke(ContainerInterface $container): TracingBuilder
    {
        $this->config = $container->get(ConfigInterface::class);
        [$app, $options, $sampler] = $this->parseConfig();
        $endpoint = Endpoint::create($app['name'], $app['ipv4'], $app['ipv6'], $app['port']);
        $reporter = new Http($container->get(HttpClientFactory::class), $options);
        return TracingBuilder::create()
            ->havingLocalEndpoint($endpoint)
            ->havingSampler($sampler)
            ->havingReporter($reporter);
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
        $prefix = 'opentracing.zipkin.';
        return $this->config->get($prefix . $key, $default);
    }
}

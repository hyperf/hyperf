<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Tracer\Adapter;

use Hyperf\Contract\ConfigInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class JaegerTracerFactory
{

    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        [$name, $options] = $this->parseConfig();
        $logger = $container->get(LoggerInterface::class);
        $cache = $container->get(CacheItemPoolInterface::class);
        $jaegerConfig = new Jaeger\Config(
            $options,
            $name,
            $logger,
            $cache,
        );
        return $jaegerConfig->initializeTracer();
    }

    private function parseConfig(): array
    {
        return [
            $this->getConfig('name', 'skeleton'),
            $this->getConfig('options', [
                'sampler' => [
                    'type' => Jaeger\SAMPLER_TYPE_CONST,
                    'param' => true,
                ],
                'logging' => true,
            ]),
        ];
    }

    private function getConfig(string $key, $default)
    {
        $prefix = 'opentracing.jaeger.';
        return $this->config->get($prefix . $key, $default);
    }
}

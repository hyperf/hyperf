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
use Hyperf\Tracer\Contract\NamedFactoryInterface;
use Jaeger\Config;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use const Jaeger\SAMPLER_TYPE_CONST;

class JaegerTracerFactory implements NamedFactoryInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var string
     */
    private $prefix;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $container->get(ConfigInterface::class);
    }

    public function make(string $name)
    {
        $this->prefix = "opentracing.tracer.{$name}.";
        [$name, $options] = $this->parseConfig();

        $logger = null;
        if ($this->container->has(LoggerInterface::class)) {
            $logger = $this->container->get(LoggerInterface::class);
        }

        $cache = null;
        if ($this->container->has(CacheItemPoolInterface::class)) {
            $cache = $this->container->get(CacheItemPoolInterface::class);
        }

        $jaegerConfig = new Config(
            $options,
            $name,
            $logger,
            $cache
        );
        return $jaegerConfig->initializeTracer();
    }

    private function parseConfig(): array
    {
        return [
            $this->getConfig('name', 'skeleton'),
            $this->getConfig('options', [
                'sampler' => [
                    'type' => SAMPLER_TYPE_CONST,
                    'param' => true,
                ],
                'logging' => false,
            ]),
        ];
    }

    private function getConfig(string $key, $default)
    {
        return $this->config->get($this->prefix . $key, $default);
    }
}

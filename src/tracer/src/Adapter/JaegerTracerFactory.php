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
use Hyperf\Tracer\Contract\NamedFactoryInterface;
use Jaeger\Config;
use OpenTracing\Tracer;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use const Jaeger\SAMPLER_TYPE_CONST;

class JaegerTracerFactory implements NamedFactoryInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var null|LoggerInterface
     */
    private $logger;

    /**
     * @var null|CacheItemPoolInterface
     */
    private $cache;

    /**
     * @var string
     */
    private $prefix;

    public function __construct(ConfigInterface $config, ?LoggerInterface $logger = null, ?CacheItemPoolInterface $cache = null)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->cache = $cache;
    }

    public function make(string $name): Tracer
    {
        $this->prefix = "opentracing.tracer.{$name}.";
        [$name, $options] = $this->parseConfig();

        $jaegerConfig = new Config(
            $options,
            $name,
            $this->logger,
            $this->cache
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

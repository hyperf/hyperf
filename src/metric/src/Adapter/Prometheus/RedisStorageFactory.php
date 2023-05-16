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
namespace Hyperf\Metric\Adapter\Prometheus;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Redis\RedisFactory;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class RedisStorageFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): Redis
    {
        $config = $container->get(ConfigInterface::class);
        $redisFactory = $container->get(RedisFactory::class);

        Redis::setPrefix($config->get('metric.metric.prometheus.redis_prefix', $config->get('app_name', 'skeleton')));
        // TODO: since 3.1, default value will be changed to ':metric_keys'
        Redis::setMetricGatherKeySuffix($config->get('metric.metric.prometheus.redis_gather_key_suffix', '_METRIC_KEYS'));

        return new Redis($redisFactory->get($config->get('metric.metric.prometheus.redis_config', 'default')));
    }
}

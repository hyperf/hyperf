<?php

namespace Hyperf\Session\Handler;


use Hyperf\Contract\ConfigInterface;
use Hyperf\Redis\RedisFactory;
use Psr\Container\ContainerInterface;

class RedisHandlerFactory
{

    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        $connection = $config->get('session.options.connection');
        $gcMaxLifetime = $config->get('session.options.gc_maxlifetime', 1200);
        $redisFactory = $container->get(RedisFactory::class);
        $redis = $redisFactory->get($connection);
        return new RedisHandler($redis, $gcMaxLifetime);
    }

}
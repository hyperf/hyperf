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

namespace HyperfTest\Cache\Stub;

use Hyperf\Cache\Driver\RedisDriver;
use Hyperf\Redis\RedisFactory;
use Psr\Container\ContainerInterface;

class SerializeRedisDriver extends RedisDriver
{
    public function __construct(ContainerInterface $container, array $config)
    {
        parent::__construct($container, $config);
        $this->redis = $container->get(RedisFactory::class)->get('serialize');
    }
}

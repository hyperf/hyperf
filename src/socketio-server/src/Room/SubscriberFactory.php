<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\SocketIOServer\Room;

use Hyperf\Contract\ContainerInterface;
use Hyperf\Redis\Redis;
use Mix\Redis\Subscribe\Subscriber;

class SubscriberFactory
{
    public function __invoke(ContainerInterface $container)
    {
        if (! class_exists(\Mix\Redis\Subscribe\Subscriber::class)) {
            return null;
        }
        $redis = $container->get(Redis::class);
        $host = $redis->getHost();
        $port = $redis->getPort();
        $pass = $redis->getAuth();

        try {
            $sub = new Subscriber($host, $port, $pass ?? '', 5);
            defer(function () use ($sub) {
                $sub->close();
            });
            return $sub;
        } catch (\Throwable $e) {
            return null;
        }
    }
}

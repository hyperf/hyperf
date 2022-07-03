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
namespace Hyperf\SocketIOServer\Room;

use Hyperf\Context\Context;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Redis\Redis;
use Mix\Redis\Subscriber\Subscriber;

class SubscriberFactory
{
    public function __invoke(ContainerInterface $container)
    {
        if (! class_exists(\Mix\Redis\Subscriber\Subscriber::class)) {
            return null;
        }
        /** @var Redis|\Redis $redis */
        $redis = Context::get(Redis::class) ?? $container->get(Redis::class);
        $host = $redis->getHost();
        $port = $redis->getPort();
        $pass = $redis->getAuth();
        $prefix = $redis->getOption(\Redis::OPT_PREFIX);

        try {
            $sub = new class($host, $port, $pass ?? '', 5) extends Subscriber {
                public string $prefix = '';

                public function subscribe(string ...$channels)
                {
                    $channels = array_map(fn ($channel) => $this->prefix . $channel, $channels);
                    parent::subscribe(...$channels);
                }
            };
            if ($prefix) {
                $sub->prefix = $prefix;
            }
            defer(function () use ($sub) {
                $sub->close();
            });
            return $sub;
        } catch (\Throwable) {
            return null;
        }
    }
}

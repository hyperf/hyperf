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

namespace Hyperf\SocketIOServer\Command;

use Hyperf\Command\Command;
use Hyperf\Redis\RedisFactory;
use Redis;
use Symfony\Component\Console\Input\InputArgument;

class RemoveRedisGarbage extends Command
{
    protected string $redisPrefix = 'ws';

    protected string $connection = 'default';

    public function __construct(private RedisFactory $factory)
    {
        parent::__construct('socketio:clear');
    }

    public function handle()
    {
        $redis = $this->factory->get($this->connection);
        $nsp = $this->input->getArgument('namespace');
        $prefix = join(':', [
            $this->redisPrefix,
            $nsp,
        ]);
        // SCAN returns full key names and adds Redis::OPT_PREFIX to the pattern only with Redis::SCAN_PREFIX, while DEL always adds it.
        $clientPrefix = (string) $redis->getOption(Redis::OPT_PREFIX);
        $pattern = "{$prefix}*";
        if ($clientPrefix !== '' && ! ((int) $redis->getOption(Redis::OPT_SCAN) & Redis::SCAN_PREFIX)) {
            $pattern = $clientPrefix . $pattern;
        }
        $iterator = null;
        while (true) {
            $keys = $redis->scan($iterator, $pattern);
            if ($keys === false) {
                return;
            }
            if (! empty($keys)) {
                if ($clientPrefix !== '') {
                    $keys = array_map(fn ($key) => substr($key, strlen($clientPrefix)), $keys);
                }
                $redis->del(...$keys);
            }
        }
    }

    protected function getArguments()
    {
        return [
            ['namespace', InputArgument::OPTIONAL, 'The namespace to be cleaned up.'],
        ];
    }
}

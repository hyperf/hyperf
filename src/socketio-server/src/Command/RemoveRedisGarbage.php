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
        $iterator = null;
        while (true) {
            $keys = $redis->scan($iterator, "{$prefix}*");
            if ($keys === false) {
                return;
            }
            if (! empty($keys)) {
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

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
use Hyperf\Redis\Redis;
use Hyperf\Redis\RedisFactory;
use Symfony\Component\Console\Input\InputArgument;

class RemoveRedisGarbage extends Command
{
    protected string $redisPrefix = 'ws';

    protected string $connection = 'default';

    /**
     * @var \Redis|Redis
     */
    private $redis;

    public function __construct(RedisFactory $factory)
    {
        parent::__construct('socketio:clear');
        $this->redis = $factory->get($this->connection);
    }

    public function handle()
    {
        $nsp = $this->input->getArgument('namespace');
        $prefix = join(':', [
            $this->redisPrefix,
            $nsp,
        ]);
        $iterator = null;
        while (true) {
            $keys = $this->redis->scan($iterator, "{$prefix}*");
            if ($keys === false) {
                return;
            }
            if (! empty($keys)) {
                $this->redis->del(...$keys);
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

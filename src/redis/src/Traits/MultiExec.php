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

namespace Hyperf\Redis\Traits;

use Hyperf\Context\Context;
use Hyperf\Redis\Redis as HyperfRedis;
use Redis;
use RedisCluster;

use function Hyperf\Tappable\tap;

trait MultiExec
{
    /**
     * Execute commands in a pipeline.
     *
     * @return array|Redis
     */
    public function pipeline(?callable $callback = null)
    {
        return $this->executeMultiExec('pipeline', $callback);
    }

    /**
     * Execute commands in a transaction.
     *
     * @return array|Redis|RedisCluster
     */
    public function transaction(?callable $callback = null)
    {
        return $this->executeMultiExec('multi', $callback);
    }

    /**
     * Execute multi-exec commands with optional callback.
     *
     * @return array|Redis|RedisCluster
     */
    private function executeMultiExec(string $command, ?callable $callback = null)
    {
        if (is_null($callback)) {
            return $this->__call($command, []);
        }

        if (! $this instanceof HyperfRedis) {
            return tap($this->__call($command, []), $callback)->exec();
        }

        $hasExistingConnection = Context::has($this->getContextKey());
        $instance = $this->__call($command, []);

        try {
            return tap($instance, $callback)->exec();
        } finally {
            if (! $hasExistingConnection) {
                $this->releaseContextConnection();
            }
        }
    }
}

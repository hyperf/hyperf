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
        $pipeline = $this->__call('pipeline', []);

        if (is_null($callback)) {
            return $pipeline;
        }

        try {
            return tap($pipeline, $callback)->exec();
        } finally {
            $this->releaseMultiExecConnection();
        }
    }

    /**
     * Execute commands in a transaction.
     *
     * @return array|Redis|RedisCluster
     */
    public function transaction(?callable $callback = null)
    {
        $transaction = $this->__call('multi', []);

        if (is_null($callback)) {
            return $transaction;
        }

        try {
            return tap($transaction, $callback)->exec();
        } finally {
            $this->releaseMultiExecConnection();
        }
    }

    /**
     * Release connection after multi-exec callback.
     */
    private function releaseMultiExecConnection(): void
    {
        $contextKey = $this->getContextKey();
        $connection = Context::get($contextKey);

        if ($connection) {
            // Only release if we're on the default database
            if (! $connection->getDatabase() || $connection->getDatabase() === $connection->getConfig()['db']) {
                Context::set($contextKey, null);
                $connection->release();
            }
        }
    }
}

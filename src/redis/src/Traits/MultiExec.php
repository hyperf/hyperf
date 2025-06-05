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
        $contextKey = $this->getContextKey();
        $hadContextConnection = Context::has($contextKey);

        $pipeline = $this->__call('pipeline', []);

        if (is_null($callback)) {
            return $pipeline;
        }

        try {
            return tap($pipeline, $callback)->exec();
        } finally {
            if (! $hadContextConnection) {
                $this->releaseMultiExecConnection();
            }
        }
    }

    /**
     * Execute commands in a transaction.
     *
     * @return array|Redis|RedisCluster
     */
    public function transaction(?callable $callback = null)
    {
        $contextKey = $this->getContextKey();
        $hadContextConnection = Context::has($contextKey);

        $transaction = $this->__call('multi', []);

        if (is_null($callback)) {
            return $transaction;
        }

        try {
            return tap($transaction, $callback)->exec();
        } finally {
            if (! $hadContextConnection) {
                $this->releaseMultiExecConnection();
            }
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
            Context::set($contextKey, null);
            $connection->release();
        }
    }
}

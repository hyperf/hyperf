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
            // Execute the pipeline and get the result
            $result = tap($pipeline, $callback)->exec();
            return $result;
        } finally {
            // Release connection explicitly
            $contextKey = $this->getContextKey();
            $connection = Context::get($contextKey);
            if ($connection) {
                Context::set($contextKey, null);
                $connection->release();
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
        $transaction = $this->__call('multi', []);

        return is_null($callback) ? $transaction : tap($transaction, $callback)->exec();
    }
}

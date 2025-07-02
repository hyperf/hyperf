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
        if (is_null($callback)) {
            return $this->__call('pipeline', []);
        }
        
        $pipeline = $this->__call('pipeline', ['using_callback' => true]);
        return tap($pipeline, $callback)->exec();
    }

    /**
     * Execute commands in a transaction.
     *
     * @return array|Redis|RedisCluster
     */
    public function transaction(?callable $callback = null)
    {
        if (is_null($callback)) {
            return $this->__call('multi', []);
        }
        
        $transaction = $this->__call('multi', ['using_callback' => true]);
        return tap($transaction, $callback)->exec();
    }
}
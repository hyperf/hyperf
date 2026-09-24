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

namespace Hyperf\Database\Events;

class ModelsPruned
{
    /**
     * Create a new event instance.
     *
     * @param string $model the class name of the model that was pruned
     * @param int $count the number of pruned records
     */
    public function __construct(
        public readonly string $model,
        public readonly int $count,
    ) {
    }
}

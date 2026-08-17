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

class ModelPruningStarting
{
    /**
     * Create a new event instance.
     *
     * @param array<class-string> $models the class names of the models that will be pruned
     */
    public function __construct(
        public array $models = []
    ) {
    }
}

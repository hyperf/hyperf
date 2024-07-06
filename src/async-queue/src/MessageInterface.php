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

namespace Hyperf\AsyncQueue;

interface MessageInterface
{
    public function job(): JobInterface;

    /**
     * Whether the queue can be handle again.
     */
    public function attempts(): bool;

    /**
     * The current attempt count.
     */
    public function getAttempts(): int;
}

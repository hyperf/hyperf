<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Contract;

interface FreqInterface
{
    /**
     * Number of hit per time.
     */
    public function hit(int $number = 1): bool;

    /**
     * Hits per second.
     */
    public function freq(): float;
}

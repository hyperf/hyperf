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

namespace Hyperf\Resource\Value;

class MissingValue implements PotentiallyMissing
{
    /**
     * Determine if the object should be considered "missing".
     */
    public function isMissing(): bool
    {
        return true;
    }
}

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

namespace HyperfTest\Snowflake\Stub;

use Hyperf\Snowflake\Snowflake;

class MaxSnowflake extends Snowflake
{
    protected function getTimestamp(): int
    {
        if ($this->level == self::LEVEL_SECOND) {
            return time();
        }
        return intval(microtime(true) * 1000);
    }
}

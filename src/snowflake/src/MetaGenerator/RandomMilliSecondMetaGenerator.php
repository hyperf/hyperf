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

namespace Hyperf\Snowflake\MetaGenerator;

use Hyperf\Snowflake\MetaGenerator;

class RandomMilliSecondMetaGenerator extends MetaGenerator
{
    public function getDataCenterId(): int
    {
        return rand(0, 31);
    }

    public function getWorkerId(): int
    {
        return rand(0, 31);
    }

    public function getTimeStamp(): int
    {
        return intval(microtime(true) * 1000);
    }

    public function getNextTimeStamp(): int
    {
        $timestamp = $this->getTimeStamp();
        while ($timestamp <= $this->lastTimeStamp) {
            $timestamp = $this->getTimeStamp();
        }

        return $timestamp;
    }
}

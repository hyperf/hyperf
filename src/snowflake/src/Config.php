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

namespace Hyperf\Snowflake;

class Config implements ConfigInterface
{
    const MILLISECOND_BITS = 41;

    const DATA_CENTER_ID_BITS = 5;

    const WORKER_ID_BITS = 5;

    const SEQUENCE_BITS = 12;

    public function maxWorkerId(): int
    {
        return -1 ^ (-1 << self::WORKER_ID_BITS);
    }

    public function maxDataCenterId(): int
    {
        return -1 ^ (-1 << self::DATA_CENTER_ID_BITS);
    }

    public function maxSequence(): int
    {
        return -1 ^ (-1 << self::SEQUENCE_BITS);
    }

    public function getTimeStampShift(): int
    {
        return self::SEQUENCE_BITS + self::WORKER_ID_BITS + self::DATA_CENTER_ID_BITS;
    }

    public function getDataCenterShift(): int
    {
        return self::SEQUENCE_BITS + self::WORKER_ID_BITS;
    }

    public function getWorkerIdShift(): int
    {
        return self::SEQUENCE_BITS;
    }

    public function getTimeStampBits(): int
    {
        return self::MILLISECOND_BITS;
    }

    public function getDataCenterBits(): int
    {
        return self::DATA_CENTER_ID_BITS;
    }

    public function getWorkerBits(): int
    {
        return self::WORKER_ID_BITS;
    }

    public function getSequenceBits(): int
    {
        return self::SEQUENCE_BITS;
    }
}

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

use Hyperf\Snowflake\Exception\SnowflakeException;

class Meta
{
    const SEQUENCE_BITS = 12;

    const MILLISECOND_BITS = 39;

    const BUSINESS_ID_BITS = 4;

    const DATA_CENTER_ID_BITS = 2;

    const MACHINE_ID_BITS = 7;

    /**
     * @var int
     */
    public $beginTimeStamp;

    /**
     * @var int [1, 15]
     */
    public $businessId;

    /**
     * @var int [1, 4]
     */
    public $dataCenterId;

    /**
     * @var int [1, 128]
     */
    public $machineId;

    /**
     * @var int [1, 4096]
     */
    public $sequence;

    public function __construct(int $beginTimeStamp, int $businessId, int $dataCenterId, int $machineId, int $sequence)
    {
        if ($businessId <= 0 || $businessId > $this->maxBusinessId()) {
            throw new SnowflakeException('Business Id can\'t be greater than 15 or less than 0');
        }
        if ($dataCenterId <= 0 || $dataCenterId > $this->maxDataCenterId()) {
            throw new SnowflakeException('DataCenter Id can\'t be greater than 4 or less than 0');
        }
        if ($machineId <= 0 || $machineId > $this->maxMachineId()) {
            throw new SnowflakeException('Machine Id can\'t be greater than 128 or less than 0');
        }
        if ($sequence <= 0 || $sequence > $this->maxSequence()) {
            throw new SnowflakeException('Sequence can\'t be greater than 4096 or less than 0');
        }

        $this->beginTimeStamp = $beginTimeStamp;
        $this->businessId = $businessId;
        $this->dataCenterId = $dataCenterId;
        $this->machineId = $machineId;
        $this->sequence = $sequence;
    }

    private function maxMachineId()
    {
        return -1 ^ (-1 << self::MACHINE_ID_BITS);
    }

    private function maxDataCenterId()
    {
        return -1 ^ (-1 << self::DATA_CENTER_ID_BITS);
    }

    private function maxBusinessId()
    {
        return -1 ^ (-1 << self::BUSINESS_ID_BITS);
    }

    private function maxSequence()
    {
        return -1 ^ (-1 << self::SEQUENCE_BITS);
    }
}

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
     * @var int [0, 15]
     */
    public $businessId;

    /**
     * @var int [0, 3]
     */
    public $dataCenterId;

    /**
     * @var int [0, 127]
     */
    public $machineId;

    /**
     * @var int [0, 4095]
     */
    public $sequence;

    /**
     * @var int seconds
     */
    public $timeInterval;

    public function __construct(int $businessId, int $dataCenterId, int $machineId, int $sequence)
    {
        if ($businessId < 0 || $businessId > $this->maxBusinessId()) {
            throw new SnowflakeException('Business Id can\'t be greater than 15 or less than 0');
        }
        if ($dataCenterId < 0 || $dataCenterId > $this->maxDataCenterId()) {
            throw new SnowflakeException('DataCenter Id can\'t be greater than 4 or less than 0');
        }
        if ($machineId < 0 || $machineId > $this->maxMachineId()) {
            throw new SnowflakeException('Machine Id can\'t be greater than 128 or less than 0');
        }
        if ($sequence < 0 || $sequence > $this->maxSequence()) {
            throw new SnowflakeException('Sequence can\'t be greater than 4096 or less than 0');
        }

        $this->businessId = $businessId;
        $this->dataCenterId = $dataCenterId;
        $this->machineId = $machineId;
        $this->sequence = $sequence;
    }

    /**
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function setTimeInterval(?int $timeInterval): self
    {
        $this->timeInterval = $timeInterval;
        return $this;
    }

    protected function maxMachineId()
    {
        return -1 ^ (-1 << self::MACHINE_ID_BITS);
    }

    protected function maxDataCenterId()
    {
        return -1 ^ (-1 << self::DATA_CENTER_ID_BITS);
    }

    protected function maxBusinessId()
    {
        return -1 ^ (-1 << self::BUSINESS_ID_BITS);
    }

    protected function maxSequence()
    {
        return -1 ^ (-1 << self::SEQUENCE_BITS);
    }
}

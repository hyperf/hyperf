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

class Meta
{
    const MILLISECOND_BITS = 41;

    const DATA_CENTER_ID_BITS = 5;

    const MACHINE_ID_BITS = 5;

    const SEQUENCE_BITS = 12;

    /**
     * @var int [0, 31]
     */
    protected $dataCenterId;

    /**
     * @var int [0, 31]
     */
    protected $workerId;

    /**
     * @var int [0, 4095]
     */
    protected $sequence;

    /**
     * @var int seconds or milliseconds
     */
    protected $timestamp = 0;

    /**
     * @var int seconds or milliseconds
     */
    protected $beginTimeStamp = 0;

    public function __construct(int $dataCenterId, int $workerId, int $sequence, int $timestamp, int $beginTimeStamp = 1560960000)
    {
        $this->dataCenterId = $dataCenterId;
        $this->workerId = $workerId;
        $this->sequence = $sequence;
        $this->timestamp = $timestamp;
        $this->beginTimeStamp = $beginTimeStamp;
    }

    public function getTimeInterval(): int
    {
        return $this->timestamp - $this->beginTimeStamp;
    }

    /**
     * @return int
     */
    public function getDataCenterId(): int
    {
        return $this->dataCenterId;
    }

    /**
     * @return int
     */
    public function getWorkerId(): int
    {
        return $this->workerId;
    }

    /**
     * @return int
     */
    public function getSequence(): int
    {
        return $this->sequence;
    }

    /**
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * @return int
     */
    public function getBeginTimeStamp(): int
    {
        return $this->beginTimeStamp;
    }
}

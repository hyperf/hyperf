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

namespace Hyperf\Snowflake;

class Meta
{
    /**
     * @var int [0, 31]
     */
    protected int $dataCenterId;

    /**
     * @var int [0, 31]
     */
    protected int $workerId;

    /**
     * @var int [0, 4095]
     */
    protected int $sequence;

    /**
     * @var int seconds or milliseconds
     */
    protected int $timestamp = 0;

    /**
     * @var int seconds or milliseconds
     */
    protected int $beginTimestamp = 0;

    public function __construct(int $dataCenterId, int $workerId, int $sequence, int $timestamp, int $beginTimestamp = 1560960000)
    {
        $this->dataCenterId = $dataCenterId;
        $this->workerId = $workerId;
        $this->sequence = $sequence;
        $this->timestamp = $timestamp;
        $this->beginTimestamp = $beginTimestamp;
    }

    public function getTimeInterval(): int
    {
        return $this->timestamp - $this->beginTimestamp;
    }

    public function getDataCenterId(): int
    {
        return $this->dataCenterId;
    }

    public function getWorkerId(): int
    {
        return $this->workerId;
    }

    public function getSequence(): int
    {
        return $this->sequence;
    }

    public function setDataCenterId(int $dataCenterId): self
    {
        $this->dataCenterId = $dataCenterId;
        return $this;
    }

    public function setWorkerId(int $workerId): self
    {
        $this->workerId = $workerId;
        return $this;
    }

    public function setSequence(int $sequence): self
    {
        $this->sequence = $sequence;
        return $this;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function getBeginTimestamp(): int
    {
        return $this->beginTimestamp;
    }
}

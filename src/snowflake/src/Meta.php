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
    const MILLISECOND_BITS = 41;

    const DATA_CENTER_ID_BITS = 5;

    const MACHINE_ID_BITS = 5;

    const SEQUENCE_BITS = 12;

    /**
     * @var int [0, 31]
     */
    public $dataCenterId;

    /**
     * @var int [0, 31]
     */
    public $workerId;

    /**
     * @var int [0, 4095]
     */
    public $sequence;

    /**
     * @var int seconds or millisecond
     */
    public $timestamp = 0;

    public function __construct(int $dataCenterId, int $workerId, int $sequence, int $timestamp)
    {
        // if ($dataCenterId < 0 || $dataCenterId > $this->maxDataCenterId()) {
        //     throw new SnowflakeException('DataCenter Id can\'t be greater than 4 or less than 0');
        // }
        // if ($machineId < 0 || $machineId > $this->maxMachineId()) {
        //     throw new SnowflakeException('Machine Id can\'t be greater than 128 or less than 0');
        // }
        // if ($sequence < 0 || $sequence > $this->maxSequence()) {
        //     throw new SnowflakeException('Sequence can\'t be greater than 4096 or less than 0');
        // }

        $this->dataCenterId = $dataCenterId;
        $this->workerId = $workerId;
        $this->sequence = $sequence;
        $this->timestamp = $timestamp;
    }
}

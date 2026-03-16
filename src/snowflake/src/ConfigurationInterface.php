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

interface ConfigurationInterface
{
    /**
     * Get the maximum worker id bits.
     */
    public function maxWorkerId(): int;

    /**
     * Get the maximum data center id bits.
     */
    public function maxDataCenterId(): int;

    /**
     * Get the maximum sequence bits.
     */
    public function maxSequence(): int;

    /**
     * Get the timestamp left shift.
     */
    public function getTimestampLeftShift(): int;

    /**
     * Get the data center id shift.
     */
    public function getDataCenterIdShift(): int;

    /**
     * Get the worker id shift.
     */
    public function getWorkerIdShift(): int;

    /**
     * Get the timestamp bits.
     */
    public function getTimestampBits(): int;

    /**
     * Get the data center id bits.
     */
    public function getDataCenterIdBits(): int;

    /**
     * Get the worker id bits.
     */
    public function getWorkerIdBits(): int;

    /**
     * Get the sequence bits.
     */
    public function getSequenceBits(): int;
}

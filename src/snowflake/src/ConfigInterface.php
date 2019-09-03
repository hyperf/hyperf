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

interface ConfigInterface
{
    public function maxWorkerId(): int;

    public function maxDataCenterId(): int;

    public function maxSequence(): int;

    public function getTimeStampShift(): int;

    public function getDataCenterShift(): int;

    public function getWorkerIdShift(): int;

    public function getTimeStampBits(): int;

    public function getDataCenterBits(): int;

    public function getWorkerBits(): int;

    public function getSequenceBits(): int;
}

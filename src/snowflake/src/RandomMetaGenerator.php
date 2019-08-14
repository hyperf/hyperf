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

class RandomMetaGenerator implements MetaGeneratorInterface
{
    protected $sequence = 0;

    public function generate(): Meta
    {
        $businessId = rand(0, 15);
        $dataCenterId = rand(0, 3);
        $machineId = rand(0, 127);
        $sequence = ($this->sequence++) % 4096;

        return new Meta($businessId, $dataCenterId, $machineId, $sequence);
    }
}

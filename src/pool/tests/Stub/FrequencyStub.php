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

namespace HyperfTest\Pool\Stub;

use Hyperf\Pool\Frequency;

class FrequencyStub extends Frequency
{
    public function setBeginTime($time)
    {
        $this->beginTime = $time;
    }

    public function setHits($hits)
    {
        $this->hits = $hits;
    }

    public function getHits()
    {
        return $this->hits;
    }
}

<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE.md
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

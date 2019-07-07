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

namespace Hyperf\DbConnection;

use Hyperf\Pool\Frequency as PoolFrequency;

class Frequency extends PoolFrequency
{
    protected $hits = [];

    protected $time = 10;

    protected $lowFreq = 5;

    protected $beginTime;

    public function __construct()
    {
        $this->beginTime = time();
    }

    public function hit(int $number = 1): bool
    {
        $this->flush();

        $now = time();
        $hit = $this->hits[$now] ?? 0;
        $this->hits[$now] = $number + $hit;

        return true;
    }

    public function freq(): float
    {
        $this->flush();

        $hits = 0;
        $count = 0;
        foreach ($this->hits as $hit) {
            ++$count;
            $hits += $hit;
        }

        return floatval($hits / $count);
    }

    public function isLowFreq(): bool
    {
        if ($this->freq() < $this->lowFreq) {
            return true;
        }
        return false;
    }

    protected function flush(): void
    {
        $now = time();
        $latest = $now - $this->time;
        foreach ($this->hits as $time => $hit) {
            if ($time < $latest) {
                unset($this->hits[$time]);
            }
        }

        if (count($this->hits) < $this->time) {
            $beginTime = $this->beginTime < $latest ? $latest : $this->beginTime;
            for ($i = $beginTime; $i < $now; ++$i) {
                $this->hits[$i] = $this->hits[$i] ?? 0;
            }
        }
    }
}

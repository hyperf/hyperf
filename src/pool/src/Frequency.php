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

namespace Hyperf\Pool;

use Hyperf\Contract\FrequencyInterface;

class Frequency implements FrequencyInterface, LowFrequencyInterface
{
    protected array $hits = [];

    /**
     * How much time do you want to calculate the frequency ?
     */
    protected int $time = 10;

    protected int $lowFrequency = 5;

    protected int $beginTime;

    protected int $lowFrequencyTime;

    protected int $lowFrequencyInterval = 60;

    public function __construct(protected ?Pool $pool = null)
    {
        $this->beginTime = time();
        $this->lowFrequencyTime = time();
    }

    public function hit(int $number = 1): bool
    {
        $this->flush();

        $now = time();
        $hit = $this->hits[$now] ?? 0;
        $this->hits[$now] = $number + $hit;

        return true;
    }

    public function frequency(): float
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

    public function isLowFrequency(): bool
    {
        $now = time();
        if ($this->lowFrequencyTime + $this->lowFrequencyInterval < $now && $this->frequency() < $this->lowFrequency) {
            $this->lowFrequencyTime = $now;
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
            $beginTime = max($this->beginTime, $latest);
            for ($i = $beginTime; $i < $now; ++$i) {
                $this->hits[$i] = $this->hits[$i] ?? 0;
            }
        }
    }
}

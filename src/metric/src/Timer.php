<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Metric;

use Hyperf\Metric\Contract\HistogramInterface;

class Timer
{
    /**
     * @var HistogramInterface
     */
    protected $histogram;

    /**
     * @var float
     */
    protected $time;

    /**
     * @var bool
     */
    private $observed = false;

    public function __construct(HistogramInterface $histogram)
    {
        $this->histogram = $histogram;
        $this->time = microtime(true);
    }

    public function __destruct()
    {
        if (! $this->observed) {
            $this->observeDuration();
        }
    }

    public function observeDuration()
    {
        $d = (float) microtime(true) - $this->time;
        if ($d < 0) {
            $d = (float) 0;
        }
        $this->histogram->observe($d);
        $this->observed = true;
    }

    public function with(string ...$labelValues): self
    {
        $this->histogram->with(...$labelValues);
        return $this;
    }
}

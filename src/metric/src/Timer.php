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

namespace Hyperf\Metric;

use Hyperf\Metric\Contract\MetricFactoryInterface;

use function Hyperf\Support\make;

/**
 * Syntax sugar class to handle time.
 */
class Timer
{
    protected float $time;

    private bool $ended = false;

    /**
     * @param array<string, string> $labels
     */
    public function __construct(protected string $name, protected ?array $labels = [])
    {
        $this->time = microtime(true);
    }

    public function __destruct()
    {
        $this->end();
    }

    public function end(?array $labels = []): void
    {
        if ($this->ended) {
            return;
        }
        foreach ($labels as $key => $value) {
            if (array_key_exists($key, $this->labels)) {
                $this->labels[$key] = $value;
            }
        }
        $histogram = make(MetricFactoryInterface::class)
            ->makeHistogram($this->name, array_keys($this->labels))
            ->with(...array_values($this->labels));
        $d = (float) microtime(true) - $this->time;
        if ($d < 0) {
            $d = 0.0;
        }
        $histogram->put($d);
        $this->ended = true;
    }
}

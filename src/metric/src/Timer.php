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

/**
 * Syntax sugar class to handle time.
 */
class Timer
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array<string,string>
     */
    protected $labels;

    /**
     * @var float
     */
    protected $time;

    /**
     * @var bool
     */
    private $ended = false;

    public function __construct(string $name, ?array $default = [])
    {
        $this->name = $name;
        $this->labels = $default;
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
            $d = (float) 0;
        }
        $histogram->put($d);
        $this->ended = true;
    }
}

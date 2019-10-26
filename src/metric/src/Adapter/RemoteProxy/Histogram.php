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

namespace Hyperf\Metric\Adapter\RemoteProxy;

use Hyperf\Metric\Contract\HistogramInterface;
use Hyperf\Process\ProcessCollector;

class Histogram implements HistogramInterface
{
    /**
     * @var string
     */
    protected const TARGET_PROCESS_NAME = 'metric';

    /**
     * @var string
     */
    public $name;

    /**
     * @var string[];
     */
    public $labelNames = [];

    /**
     * @var string[]
     */
    public $labelValues = [];

    /**
     * @var float
     */
    public $value;

    public function __construct(string $name, array $labelNames)
    {
        $this->name = $name;
        $this->labelNames = $labelNames;
    }

    public function with(string ...$labelValues): HistogramInterface
    {
        $this->labelValues = $labelValues;
        return $this;
    }

    public function observe(float $value): void
    {
        $this->value = $value;
        $process = ProcessCollector::get(static::TARGET_PROCESS_NAME)[0];
        $process->write(serialize($this));
    }
}

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
namespace Hyperf\Metric\Adapter\RemoteProxy;

use Hyperf\Metric\Contract\HistogramInterface;
use Hyperf\Process\ProcessCollector;

class Histogram implements HistogramInterface
{
    protected const TARGET_PROCESS_NAME = 'metric';

    /**
     * @var string[]
     */
    public array $labelValues = [];

    public float $sample;

    public function __construct(public string $name, public array $labelNames)
    {
    }

    public function with(string ...$labelValues): static
    {
        $this->labelValues = $labelValues;
        return $this;
    }

    public function put(float $sample): void
    {
        $this->sample = $sample;
        $process = ProcessCollector::get(static::TARGET_PROCESS_NAME)[0];
        $process->write(serialize($this));
    }
}

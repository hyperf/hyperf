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

use Hyperf\Metric\Contract\CounterInterface;
use Hyperf\Process\ProcessCollector;

class Counter implements CounterInterface
{
    /**
     * @var string
     */
    protected const TARGET_PROCESS_NAME = 'metric';

    /**
     * @var string
     */
    public $name;

    public $labelNames = [];

    /**
     * @var string[]
     */
    public $labelValues = [];

    /**
     * @var int
     */
    public $delta;

    public function __construct(string $name, array $labelNames)
    {
        $this->name = $name;
        $this->labelNames = $labelNames;
    }

    public function with(string ...$labelValues): CounterInterface
    {
        $this->labelValues = $labelValues;
        return $this;
    }

    public function add(int $delta): void
    {
        $this->delta = $delta;
        $process = ProcessCollector::get(static::TARGET_PROCESS_NAME)[0];
        $process->write(serialize($this));
    }
}

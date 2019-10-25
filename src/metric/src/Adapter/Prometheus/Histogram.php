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

namespace Hyperf\Metric\Adapter\Prometheus;

use Hyperf\Metric\Contract\HistogramInterface;
use Prometheus\CollectorRegistry;

class Histogram implements HistogramInterface
{
    /**
     * @var \Prometheus\CollectorRegistry
     */
    protected $registry;

    /**
     * @var \Prometheus\Histogram
     */
    protected $histogram;

    /**
     * @var string[]
     */
    protected $labelValues = [];

    public function __construct(CollectorRegistry $registry, string $namespace, string $name, string $help, array $labelNames)
    {
        $this->registry = $registry;
        $this->histogram = $registry->getOrRegisterHistogram($namespace, $name, $help, $labelNames);
    }

    public function with(string ...$labelValues): HistogramInterface
    {
        $this->labelValues = $labelValues;
        return $this;
    }

    public function observe(float $value)
    {
        $this->histogram->observe($value, $this->labelValues);
    }
}

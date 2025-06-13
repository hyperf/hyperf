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

namespace Hyperf\Metric\Contract;

interface MetricFactoryInterface
{
    /**
     * Create a Counter.
     * @param string $name name of the metric
     * @param string[] $labelNames key of your label kvs
     */
    public function makeCounter(string $name, ?array $labelNames = []): CounterInterface;

    /**
     * Create a Gauge.
     * @param string $name name of the metric
     * @param string[] $labelNames key of your label kvs
     */
    public function makeGauge(string $name, ?array $labelNames = []): GaugeInterface;

    /**
     * Create a HistogramInterface.
     * @param string $name name of the metric
     * @param string[] $labelNames key of your label kvs
     */
    public function makeHistogram(string $name, ?array $labelNames = []): HistogramInterface;

    /**
     * Handle the metric collecting/reporting/serving tasks.
     */
    public function handle(): void;
}

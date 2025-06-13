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

use Hyperf\Context\ApplicationContext;
use Hyperf\Metric\Contract\HistogramInterface;
use Hyperf\Metric\Contract\MetricCollectorInterface;

class Histogram implements HistogramInterface
{
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

        ApplicationContext::getContainer()
            ->get(MetricCollectorInterface::class)
            ->add($this);
    }
}

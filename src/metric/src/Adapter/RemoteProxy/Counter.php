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
use Hyperf\Metric\Contract\CounterInterface;
use Hyperf\Metric\Contract\MetricCollectorInterface;

class Counter implements CounterInterface
{
    /**
     * @var string[]
     */
    public array $labelValues = [];

    public ?int $delta = null;

    public function __construct(public string $name, public array $labelNames)
    {
    }

    public function with(string ...$labelValues): static
    {
        $this->labelValues = $labelValues;
        return $this;
    }

    public function add(int $delta): void
    {
        $this->delta = $delta;

        ApplicationContext::getContainer()
            ->get(MetricCollectorInterface::class)
            ->add($this);
    }
}

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
namespace Hyperf\Metric\Adapter\Prometheus;

use Hyperf\Metric\Contract\CounterInterface;
use Prometheus\CollectorRegistry;
use Prometheus\Exception\MetricsRegistrationException;

class Counter implements CounterInterface
{
    protected \Prometheus\Counter $counter;

    /**
     * @var string[]
     */
    protected array $labelValues = [];

    /**
     * @throws MetricsRegistrationException
     */
    public function __construct(protected CollectorRegistry $registry, string $namespace, string $name, string $help, array $labelNames)
    {
        $this->counter = $registry->getOrRegisterCounter($namespace, $name, $help, $labelNames);
    }

    public function with(string ...$labelValues): static
    {
        $this->labelValues = $labelValues;
        return $this;
    }

    public function add(int $delta): void
    {
        $this->counter->incBy($delta, $this->labelValues);
    }
}

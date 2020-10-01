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
namespace Hyperf\Metric\Adapter\StatsD;

use Domnikl\Statsd\Client;
use Hyperf\Metric\Contract\GaugeInterface;

class Gauge implements GaugeInterface
{
    /**
     * @var \Domnikl\Statsd\Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var float
     */
    protected $sampleRate;

    /**
     * @var string[]
     */
    protected $labelNames = [];

    /**
     * @var string[]
     */
    protected $labelValues = [];

    public function __construct(Client $client, string $name, float $sampleRate, array $labelNames)
    {
        $this->client = $client;
        $this->name = $name;
        $this->sampleRate = $sampleRate;
        $this->labelNames = $labelNames;
    }

    public function with(string ...$labelValues): GaugeInterface
    {
        $this->labelValues = $labelValues;
        return $this;
    }

    public function set(float $value): void
    {
        if ($value < 0) {
            // StatsD gauge doesn't support negative values.
            $value = 0;
        }
        $this->client->gauge($this->name, (string) $value, array_combine($this->labelNames, $this->labelValues));
    }

    public function add(float $delta): void
    {
        if ($delta >= 0) {
            $deltaStr = '+' . $delta;
        } else {
            $deltaStr = (string) $delta;
        }
        $this->client->gauge($this->name, $deltaStr, array_combine($this->labelNames, $this->labelValues));
    }
}

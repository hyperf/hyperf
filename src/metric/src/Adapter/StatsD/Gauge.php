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

    public function set(float $value)
    {
        $this->client->gauge($this->name, (int) $value, array_combine($this->labelNames, $this->labelValues));
    }

    public function add(float $delta)
    {
        if ($delta >= 0) {
            $delta = '+' + $delta;
        }
        $this->client->gauge($this->name, (int) $delta, array_combine($this->labelNames, $this->labelValues));
    }
}

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
use Hyperf\Metric\Contract\HistogramInterface;

class Histogram implements HistogramInterface
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

    public function with(string ...$labelValues): HistogramInterface
    {
        $this->labelValues = $labelValues;
        return $this;
    }

    public function put(float $sample): void
    {
        $this->client->timing($this->name, $sample, $this->sampleRate, array_combine($this->labelNames, $this->labelValues));
    }
}

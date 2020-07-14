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
namespace Hyperf\Snowflake;

use Hyperf\Snowflake\Exception\SnowflakeException;

abstract class MetaGenerator implements MetaGeneratorInterface
{
    /**
     * @var ConfigurationInterface
     */
    protected $configuration;

    protected $sequence = 0;

    protected $lastTimestamp = 0;

    protected $beginTimestamp = 0;

    public function __construct(ConfigurationInterface $configuration, int $beginTimestamp)
    {
        $this->configuration = $configuration;
        $this->lastTimestamp = $this->getTimestamp();
        $this->beginTimestamp = $beginTimestamp;
    }

    public function generate(): Meta
    {
        $timestamp = $this->getTimestamp();

        if ($timestamp == $this->lastTimestamp) {
            $this->sequence = ($this->sequence + 1) % $this->configuration->maxSequence();
            if ($this->sequence == 0) {
                $timestamp = $this->getNextTimestamp();
            }
        } else {
            $this->sequence = 0;
        }

        if ($timestamp < $this->lastTimestamp) {
            $this->clockMovedBackwards($timestamp, $this->lastTimestamp);
        }

        if ($timestamp < $this->beginTimestamp) {
            throw new SnowflakeException(sprintf('The beginTimestamp %d is invalid, because it smaller than timestamp %d.', $this->beginTimestamp, $timestamp));
        }

        $this->lastTimestamp = $timestamp;

        return new Meta($this->getDataCenterId(), $this->getWorkerId(), $this->sequence, $timestamp, $this->beginTimestamp);
    }

    public function getBeginTimestamp(): int
    {
        return $this->beginTimestamp;
    }

    public function getConfiguration(): ConfigurationInterface
    {
        return $this->configuration;
    }

    abstract public function getDataCenterId(): int;

    abstract public function getWorkerId(): int;

    abstract public function getTimestamp(): int;

    abstract public function getNextTimestamp(): int;

    protected function clockMovedBackwards($timestamp, $lastTimestamp)
    {
        throw new SnowflakeException(sprintf('Clock moved backwards. Refusing to generate id for %d milliseconds.', $lastTimestamp - $timestamp));
    }
}

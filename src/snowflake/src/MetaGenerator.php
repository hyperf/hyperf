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

namespace Hyperf\Snowflake;

use Hyperf\Snowflake\Exception\SnowflakeException;

abstract class MetaGenerator implements MetaGeneratorInterface
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    protected $sequence = 0;

    protected $lastTimeStamp = 0;

    protected $beginTimeStamp = 0;

    public function __construct(ConfigInterface $config, int $beginTimeStamp = self::DEFAULT_BEGIN_SECOND)
    {
        $this->config = $config;
        $this->lastTimeStamp = $this->getTimeStamp();
        $this->beginTimeStamp = $this->getBeginTimeStampFromSeconds($beginTimeStamp);
    }

    public function generate(): Meta
    {
        $timestamp = $this->getTimeStamp();

        if ($timestamp < $this->lastTimeStamp) {
            throw new SnowflakeException(sprintf('Clock moved backwards. Refusing to generate id for %d milliseconds.', $this->lastTimeStamp - $timestamp));
        }

        if ($timestamp == $this->lastTimeStamp) {
            $this->sequence = ($this->sequence + 1) % $this->config->maxSequence();
            if ($this->sequence == 0) {
                $timestamp = $this->getNextTimeStamp();
            }
        } else {
            $this->sequence = 0;
        }

        if ($timestamp < $this->beginTimeStamp) {
            throw new SnowflakeException(sprintf('The beginTimeStamp %d is invalid, because it smaller than timestamp %d.', $this->beginTimeStamp, $timestamp));
        }

        return new Meta($this->getDataCenterId(), $this->getWorkerId(), $this->sequence, $timestamp, $this->beginTimeStamp);
    }

    public function getBeginTimeStamp(): int
    {
        return $this->beginTimeStamp;
    }

    abstract public function getDataCenterId(): int;

    abstract public function getWorkerId(): int;

    abstract public function getTimeStamp(): int;

    abstract public function getNextTimeStamp(): int;

    abstract protected function getBeginTimeStampFromSeconds(int $seconds): int;
}

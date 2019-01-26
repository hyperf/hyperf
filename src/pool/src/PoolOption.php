<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Pool;

use Hyperf\Contract\PoolOptionInterface;

class PoolOption implements PoolOptionInterface
{
    /**
     * Min connections of pool.
     * This means the pool will create $minConnections connections when
     * pool initialization.
     *
     * @var int
     */
    private $minConnections = 1;

    /**
     * Max connections of pool.
     *
     * @var int
     */
    private $maxConnections = 10;

    /**
     * The timeout of connect the connection.
     * Default value is 10 seconds.
     *
     * @var float
     */
    private $connectTimeout = 10.0;

    /**
     * The timeout of pop a connection.
     * Default value is 3 seconds.
     *
     * @var float
     */
    private $waitTimeout = 3.0;

    /**
     * Heartbeat of connection.
     * If the value is 10, then means 10 seconds.
     * If the value is -1, then means does not need the heartbeat.
     * Default value is -1.
     *
     * @var float
     */
    private $heartbeat = -1;

    public function getMaxConnections(): int
    {
        return $this->maxConnections;
    }

    public function setMaxConnections(int $maxConnections): self
    {
        $this->maxConnections = $maxConnections;
        return $this;
    }

    public function getMinConnections(): int
    {
        return $this->minConnections;
    }

    public function setMinConnections(int $minConnections): self
    {
        $this->minConnections = $minConnections;
        return $this;
    }

    public function getConnectTimeout(): float
    {
        return $this->connectTimeout;
    }

    public function setConnectTimeout(float $connectTimeout): self
    {
        $this->connectTimeout = $connectTimeout;
        return $this;
    }

    public function getHeartbeat(): float
    {
        return $this->heartbeat;
    }

    public function setHeartbeat(float $heartbeat): self
    {
        $this->heartbeat = $heartbeat;
        return $this;
    }

    public function getWaitTimeout(): float
    {
        return $this->waitTimeout;
    }

    public function setWaitTimeout(float $waitTimeout): self
    {
        $this->waitTimeout = $waitTimeout;
        return $this;
    }
}

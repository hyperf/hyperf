<?php

namespace Hyperf\Pool;


class PoolOption
{

    /**
     * Min connections of pool.
     * This means the pool will create $minConnections connections when
     * pool initialization.
     *
     * @var int
     */
    private $minConnections = 0;

    /**
     * Max connections of pool.
     *
     * @var int
     */
    private $maxConnections = 0;

    /**
     * The timeout of connect the connection.
     * Default value is 10 seconds.
     *
     * @var int
     */
    private $connectTimeout = 10;

    /**
     * Heartbeat of connection.
     * If the value is 10, then means 10 seconds.
     * If the value is -1, then means does not need the heartbeat.
     * Default value is -1.
     *
     * @var int
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

    public function getConnectTimeout(): int
    {
        return $this->connectTimeout;
    }

    public function setConnectTimeout(int $connectTimeout): self
    {
        $this->connectTimeout = $connectTimeout;
        return $this;
    }

    public function getHeartbeat(): int
    {
        return $this->heartbeat;
    }

    public function setHeartbeat(int $heartbeat): self
    {
        $this->heartbeat = $heartbeat;
        return $this;
    }

}
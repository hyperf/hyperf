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

namespace Hyperf\Amqp;

class Params
{
    protected bool $insist = false;

    protected string $loginMethod = 'AMQPLAIN';

    protected string $locale = 'en_US';

    protected int $connectionTimeout = 3;

    protected int $readWriteTimeout = 3;

    protected bool $keepalive = false;

    protected int $heartbeat = 0;

    protected bool $closeOnDestruct = true;

    protected float $channelRpcTimeout = 0.0;

    protected int $maxIdleChannels = 10;

    public function __construct(array $data)
    {
        if (isset($data['insist'])) {
            $this->setInsist($data['insist']);
        }

        if (isset($data['login_method'])) {
            $this->setLoginMethod($data['login_method']);
        }

        if (isset($data['locale'])) {
            $this->setLocale($data['locale']);
        }

        if (isset($data['connection_timeout'])) {
            $this->setConnectionTimeout((int) $data['connection_timeout']);
        }

        if (isset($data['read_write_timeout'])) {
            $this->setReadWriteTimeout((int) $data['read_write_timeout']);
        }

        if (isset($data['keepalive'])) {
            $this->setKeepalive($data['keepalive']);
        }

        if (isset($data['heartbeat'])) {
            $this->setHeartbeat($data['heartbeat']);
        }

        if (isset($data['close_on_destruct'])) {
            $this->setCloseOnDestruct($data['close_on_destruct']);
        }

        if (isset($data['channel_rpc_timeout'])) {
            $this->setChannelRpcTimeout($data['channel_rpc_timeout']);
        }

        if (isset($data['max_idle_channels'])) {
            $this->setMaxIdleChannels((int) $data['max_idle_channels']);
        }
    }

    public function isInsist(): bool
    {
        return $this->insist;
    }

    public function getLoginMethod(): string
    {
        return $this->loginMethod;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getConnectionTimeout(): int
    {
        return $this->connectionTimeout;
    }

    public function getReadWriteTimeout(): int
    {
        return $this->readWriteTimeout;
    }

    public function isKeepalive(): bool
    {
        return $this->keepalive;
    }

    public function getHeartbeat(): int
    {
        return $this->heartbeat;
    }

    public function isCloseOnDestruct(): bool
    {
        return $this->closeOnDestruct;
    }

    public function setCloseOnDestruct(bool $closeOnDestruct): static
    {
        $this->closeOnDestruct = $closeOnDestruct;
        return $this;
    }

    public function setInsist(bool $insist): static
    {
        $this->insist = $insist;
        return $this;
    }

    public function setLoginMethod(string $loginMethod): static
    {
        $this->loginMethod = $loginMethod;
        return $this;
    }

    public function setLocale(string $locale): static
    {
        $this->locale = $locale;
        return $this;
    }

    public function setConnectionTimeout(int $connectionTimeout): static
    {
        $this->connectionTimeout = $connectionTimeout;
        return $this;
    }

    public function setReadWriteTimeout(int $readWriteTimeout): static
    {
        $this->readWriteTimeout = $readWriteTimeout;
        return $this;
    }

    public function setKeepalive(bool $keepalive): static
    {
        $this->keepalive = $keepalive;
        return $this;
    }

    public function setHeartbeat(int $heartbeat): static
    {
        $this->heartbeat = $heartbeat;
        return $this;
    }

    public function getChannelRpcTimeout(): float
    {
        return $this->channelRpcTimeout;
    }

    public function setChannelRpcTimeout(float $channelRpcTimeout): static
    {
        $this->channelRpcTimeout = $channelRpcTimeout;
        return $this;
    }

    public function getMaxIdleChannels(): int
    {
        return $this->maxIdleChannels;
    }

    public function setMaxIdleChannels(int $maxIdleChannels): static
    {
        $this->maxIdleChannels = $maxIdleChannels;
        return $this;
    }
}

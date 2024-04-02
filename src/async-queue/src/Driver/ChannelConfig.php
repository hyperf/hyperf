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

namespace Hyperf\AsyncQueue\Driver;

use Hyperf\AsyncQueue\Exception\InvalidQueueException;

class ChannelConfig
{
    protected string $channel;

    /**
     * Key for waiting message.
     */
    protected string $waiting;

    /**
     * Key for reserved message.
     */
    protected string $reserved;

    /**
     * Key for reserve timeout message.
     */
    protected string $timeout;

    /**
     * Key for delayed message.
     */
    protected string $delayed;

    /**
     * Key for failed message.
     */
    protected string $failed;

    public function __construct(string $channel)
    {
        $this->channel = $channel;
        $this->waiting = "{$channel}:waiting";
        $this->reserved = "{$channel}:reserved";
        $this->delayed = "{$channel}:delayed";
        $this->failed = "{$channel}:failed";
        $this->timeout = "{$channel}:timeout";
    }

    public function get(string $queue)
    {
        if (isset($this->{$queue}) && is_string($this->{$queue})) {
            return $this->{$queue};
        }

        throw new InvalidQueueException(sprintf('Queue %s is not exist.', $queue));
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function setChannel(string $channel): static
    {
        $this->channel = $channel;
        return $this;
    }

    public function getWaiting(): string
    {
        return $this->waiting;
    }

    public function setWaiting(string $waiting): static
    {
        $this->waiting = $waiting;
        return $this;
    }

    public function getReserved(): string
    {
        return $this->reserved;
    }

    public function setReserved(string $reserved): static
    {
        $this->reserved = $reserved;
        return $this;
    }

    public function getTimeout(): string
    {
        return $this->timeout;
    }

    public function setTimeout(string $timeout): static
    {
        $this->timeout = $timeout;
        return $this;
    }

    public function getDelayed(): string
    {
        return $this->delayed;
    }

    public function setDelayed(string $delayed): static
    {
        $this->delayed = $delayed;
        return $this;
    }

    public function getFailed(): string
    {
        return $this->failed;
    }

    public function setFailed(string $failed): static
    {
        $this->failed = $failed;
        return $this;
    }
}

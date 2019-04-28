<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\AsyncQueue\Driver;

class ChannelConfig
{
    /**
     * @var string
     */
    protected $channel;

    /**
     * Key for waiting message.
     *
     * @var string
     */
    protected $waiting;

    /**
     * Key for reserved message.
     *
     * @var string
     */
    protected $reserved;

    /**
     * Key for reserve timeout message.
     *
     * @var string
     */
    protected $timeout;

    /**
     * Key for delayed message.
     *
     * @var string
     */
    protected $delayed;

    /**
     * Key for failed message.
     *
     * @var string
     */
    protected $failed;

    public function __construct(string $channel)
    {
        $this->channel = $channel;
        $this->waiting = "{$channel}:waiting";
        $this->reserved = "{$channel}:reserved";
        $this->delayed = "{$channel}:delayed";
        $this->failed = "{$channel}:failed";
        $this->timeout = "{$channel}:timeout";
    }

    /**
     * @return string
     */
    public function getChannel(): string
    {
        return $this->channel;
    }

    /**
     * @param string $channel
     * @return QueueConfig
     */
    public function setChannel(string $channel): QueueConfig
    {
        $this->channel = $channel;
        return $this;
    }

    /**
     * @return string
     */
    public function getWaiting(): string
    {
        return $this->waiting;
    }

    /**
     * @param string $waiting
     * @return QueueConfig
     */
    public function setWaiting(string $waiting): QueueConfig
    {
        $this->waiting = $waiting;
        return $this;
    }

    /**
     * @return string
     */
    public function getReserved(): string
    {
        return $this->reserved;
    }

    /**
     * @param string $reserved
     * @return QueueConfig
     */
    public function setReserved(string $reserved): QueueConfig
    {
        $this->reserved = $reserved;
        return $this;
    }

    /**
     * @return string
     */
    public function getTimeout(): string
    {
        return $this->timeout;
    }

    /**
     * @param string $timeout
     * @return QueueConfig
     */
    public function setTimeout(string $timeout): QueueConfig
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * @return string
     */
    public function getDelayed(): string
    {
        return $this->delayed;
    }

    /**
     * @param string $delayed
     * @return QueueConfig
     */
    public function setDelayed(string $delayed): QueueConfig
    {
        $this->delayed = $delayed;
        return $this;
    }

    /**
     * @return string
     */
    public function getFailed(): string
    {
        return $this->failed;
    }

    /**
     * @param string $failed
     * @return QueueConfig
     */
    public function setFailed(string $failed): QueueConfig
    {
        $this->failed = $failed;
        return $this;
    }
}

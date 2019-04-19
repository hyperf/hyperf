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

namespace Hyperf\AsyncQueue\Driver;

use Hyperf\AsyncQueue\JobInterface;
use Hyperf\AsyncQueue\Message;
use Hyperf\AsyncQueue\MessageInterface;
use Psr\Container\ContainerInterface;
use Redis;

class RedisDriver extends Driver
{
    /**
     * @var \Redis
     */
    protected $redis;

    /**
     * @var ChannelConfig
     */
    protected $channel;

    /**
     * Max polling time.
     * @var int
     */
    protected $timeout;

    /**
     * Retry delay time.
     * @var int
     */
    protected $retrySeconds;

    /**
     * Handle timeout.
     * @var int
     */
    protected $handleTimeout;

    public function __construct(ContainerInterface $container, $config)
    {
        parent::__construct($container, $config);
        $channel = $config['channel'] ?? 'queue';

        $this->redis = $container->get(Redis::class);
        $this->timeout = $config['timeout'] ?? 5;
        $this->retrySeconds = $config['retry_seconds'] ?? 10;
        $this->handleTimeout = $config['handle_timeout'] ?? 10;

        $this->channel = make(ChannelConfig::class, ['channel' => $channel]);
    }

    public function push(JobInterface $job): bool
    {
        $message = new Message($job);
        $data = $this->packer->pack($message);
        return (bool) $this->redis->lPush($this->channel->getWaiting(), $data);
    }

    public function delay(JobInterface $job, int $delay = 0): bool
    {
        if ($delay === 0) {
            return $this->push($job);
        }

        $message = new Message($job);
        $data = $this->packer->pack($message);
        return $this->redis->zAdd($this->channel->getDelayed(), time() + $delay, $data) > 0;
    }

    public function pop(): array
    {
        $this->move($this->channel->getDelayed(), $this->channel->getWaiting());
        $this->move($this->channel->getReserved(), $this->channel->getTimeout());

        $res = $this->redis->brPop($this->channel->getWaiting(), $this->timeout);
        if (! isset($res[1])) {
            return [false, null];
        }

        $data = $res[1];
        $message = $this->packer->unpack($data);
        if (! $message) {
            return [false, null];
        }

        $this->redis->zadd($this->channel->getReserved(), time() + $this->handleTimeout, $data);

        return [$data, $message];
    }

    public function ack($data): bool
    {
        return $this->remove($data);
    }

    public function fail($data): bool
    {
        if ($this->remove($data)) {
            return (bool) $this->redis->lPush($this->channel->getFailed(), $data);
        }
        return false;
    }

    public function reload(): int
    {
        $num = 0;
        while ($this->redis->rpoplpush($this->channel->getFailed(), $this->channel->getWaiting())) {
            ++$num;
        }
        return $num;
    }

    public function flush(): bool
    {
        return (bool) $this->redis->delete($this->channel->getFailed());
    }

    public function info(): array
    {
        return [
            'waiting' => $this->redis->lLen($this->channel->getWaiting()),
            'delayed' => $this->redis->zCard($this->channel->getDelayed()),
            'failed' => $this->redis->lLen($this->channel->getFailed()),
        ];
    }

    protected function retry(MessageInterface $message): bool
    {
        $data = $this->packer->pack($message);
        return $this->redis->zAdd($this->channel->getDelayed(), time() + $this->retrySeconds, $data) > 0;
    }

    /**
     * Remove data from reserved queue.
     * @param mixed $data
     */
    protected function remove($data): bool
    {
        return $this->redis->zrem($this->channel->getReserved(), $data) > 0;
    }

    /**
     * Move message to the waiting queue.
     */
    protected function move(string $from, string $to): void
    {
        $now = time();
        if ($expired = $this->redis->zrevrangebyscore($from, (string) $now, '-inf')) {
            foreach ($expired as $job) {
                if ($this->redis->zRem($from, $job) > 0) {
                    $this->redis->lPush($to, $job);
                }
            }
        }
    }
}

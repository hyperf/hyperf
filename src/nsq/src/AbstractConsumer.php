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

namespace Hyperf\Nsq;

use Psr\Container\ContainerInterface;

abstract class AbstractConsumer
{
    protected string $pool = 'default';

    protected string $topic = '';

    protected string $channel = '';

    protected string $name = 'NsqConsumer';

    protected int $nums = 1;

    public function __construct(protected ContainerInterface $container)
    {
    }

    abstract public function consume(Message $message): ?string;

    public function getTopic(): string
    {
        return $this->topic;
    }

    public function setTopic(string $topic): static
    {
        $this->topic = $topic;
        return $this;
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getNums(): int
    {
        return $this->nums;
    }

    public function setNums(int $nums): static
    {
        $this->nums = $nums;
        return $this;
    }

    public function getPool(): string
    {
        return $this->pool;
    }

    public function setPool(string $pool): static
    {
        $this->pool = $pool;
        return $this;
    }

    public function isEnable(): bool
    {
        return true;
    }
}

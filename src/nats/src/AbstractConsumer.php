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

namespace Hyperf\Nats;

use Psr\Container\ContainerInterface;

abstract class AbstractConsumer
{
    public string $pool = 'default';

    protected string $subject = '';

    protected string $queue = '';

    protected string $name = 'NatsConsumer';

    protected int $nums = 1;

    public function __construct(protected ContainerInterface $container)
    {
    }

    abstract public function consume(Message $payload);

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): static
    {
        $this->subject = $subject;
        return $this;
    }

    public function getQueue(): string
    {
        return $this->queue;
    }

    public function setQueue(string $queue): static
    {
        $this->queue = $queue;
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
}

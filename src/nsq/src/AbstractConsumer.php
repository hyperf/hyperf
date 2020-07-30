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
    /**
     * @var string
     */
    protected $pool = 'default';

    /**
     * @var string
     */
    protected $topic = '';

    /**
     * @var string
     */
    protected $channel = '';

    /**
     * @var string
     */
    protected $name = 'NsqConsumer';

    /**
     * @var int
     */
    protected $nums = 1;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    abstract public function consume(Message $message): ?string;

    public function getTopic(): string
    {
        return $this->topic;
    }

    public function setTopic(string $topic): self
    {
        $this->topic = $topic;
        return $this;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function setChannel(string $channel): self
    {
        $this->channel = $channel;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getNums(): int
    {
        return $this->nums;
    }

    public function setNums(int $nums): self
    {
        $this->nums = $nums;
        return $this;
    }

    public function getPool(): string
    {
        return $this->pool;
    }

    public function setPool(string $pool): self
    {
        $this->pool = $pool;
        return $this;
    }

    public function isEnable(): bool
    {
        return true;
    }
}

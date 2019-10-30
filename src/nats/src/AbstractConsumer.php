<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Nats;

use Nats\Message;
use Psr\Container\ContainerInterface;

abstract class AbstractConsumer
{
    /**
     * @var string
     */
    public $pool = 'default';

    /**
     * @var string
     */
    protected $subject = '';

    /**
     * @var string
     */
    protected $name = 'NatsConsumer';

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

    abstract public function handle(Message $payload);

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;
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
}

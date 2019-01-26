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

namespace Hyperf\Amqp\Message;

use Hyperf\Amqp\DeclareBuilder\ExchangeDeclareBuilder;
use Hyperf\Amqp\Exceptions\MessageException;
use Psr\Container\ContainerInterface;

abstract class Message implements MessageInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var string
     */
    protected $poolName = 'default';

    protected $exchange;

    protected $type = Type::TOPIC;

    protected $routingKey;

    // $passive = false,
    // $durable = false,
    // $auto_delete = true,
    // $internal = false,
    // $nowait = false,
    // $arguments = array(),
    // $ticket = null
    public function getType(): string
    {
        return $this->type;
    }

    public function getExchange(): string
    {
        return $this->exchange;
    }

    public function getRoutingKey(): string
    {
        return $this->routingKey;
    }

    public function getPoolName(): string
    {
        return $this->poolName;
    }

    public function getExchangeDeclareBuilder(): ExchangeDeclareBuilder
    {
        return (new ExchangeDeclareBuilder())
            ->setExchange($this->getExchange())
            ->setType($this->getType())
        ;
    }

    public function serialize(): string
    {
        throw new MessageException('You must rewrite this method.');
    }

    public function unserialize(string $data)
    {
        throw new MessageException('You must rewrite this method.');
    }
}

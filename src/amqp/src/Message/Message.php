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

use Hyperf\Amqp\Builder\ExchangeBuilder;
use Hyperf\Amqp\Exception\MessageException;

abstract class Message implements MessageInterface
{

    /**
     * @var string
     */
    protected $poolName = 'default';

    /**
     * @var string
     */
    protected $exchange = '';

    /**
     * @var string
     */
    protected $type = Type::TOPIC;

    /**
     * @var string
     */
    protected $routingKey = '';

    // $passive = false,
    // $durable = false,
    // $auto_delete = true,
    // $internal = false,
    // $nowait = false,
    // $arguments = array(),
    // $ticket = null

    public function setType(string $type): self
    {
        if (! in_array($type, Type::all())) {
            throw new \InvalidArgumentException(sprintf('Invalid type %s, available valus [%s]', $type, implode(',', Type::all())));
        }
        $this->type = $type;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setExchange(string $exchange): self
    {
        $this->exchange = $exchange;
        return $this;
    }

    public function getExchange(): string
    {
        return $this->exchange;
    }

    public function setRoutingKey(string $routingKey): self
    {
        $this->routingKey = $routingKey;
        return $this;
    }

    public function getRoutingKey(): string
    {
        return $this->routingKey;
    }

    public function getPoolName(): string
    {
        return $this->poolName;
    }

    public function getExchangeBuilder(): ExchangeBuilder
    {
        return (new ExchangeBuilder())->setExchange($this->getExchange())->setType($this->getType());
    }

    public function serialize(): string
    {
        throw new MessageException('You have to overwrite serialize() method.');
    }

    public function unserialize(string $data)
    {
        throw new MessageException('You have to overwrite unserialize() method.');
    }
}

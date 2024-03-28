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

namespace Hyperf\Amqp\Message;

use Hyperf\Amqp\Builder\ExchangeBuilder;
use Hyperf\Amqp\Exception\MessageException;

abstract class Message implements MessageInterface
{
    protected string $poolName = 'default';

    protected string $exchange = '';

    protected string|Type $type = Type::TOPIC;

    protected array|string $routingKey = '';

    public function setType(string|Type $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getType(): string|Type
    {
        return $this->type;
    }

    public function getTypeString(): string
    {
        return $this->type instanceof Type ? $this->type->value : $this->type;
    }

    public function setExchange(string $exchange): static
    {
        $this->exchange = $exchange;
        return $this;
    }

    public function getExchange(): string
    {
        return $this->exchange;
    }

    public function setRoutingKey($routingKey): static
    {
        $this->routingKey = $routingKey;
        return $this;
    }

    public function getRoutingKey(): array|string
    {
        return $this->routingKey;
    }

    public function setPoolName(string $name): static
    {
        $this->poolName = $name;
        return $this;
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

<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
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
     * @var array|string
     */
    protected $routingKey = '';

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

    public function setRoutingKey($routingKey): self
    {
        $this->routingKey = $routingKey;
        return $this;
    }

    public function getRoutingKey()
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

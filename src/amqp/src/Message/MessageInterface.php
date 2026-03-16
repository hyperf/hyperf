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

interface MessageInterface
{
    public function setPoolName(string $name);

    /**
     * Pool name for amqp.
     */
    public function getPoolName(): string;

    public function setType(string|Type $type);

    public function getType(): string|Type;

    public function setExchange(string $exchange);

    public function getExchange(): string;

    public function setRoutingKey(array|string $routingKey);

    public function getRoutingKey(): array|string;

    public function getExchangeBuilder(): ExchangeBuilder;

    /**
     * Serialize the message body to a string.
     */
    public function serialize(): string;

    /**
     * Unserialize the message body.
     */
    public function unserialize(string $data);
}

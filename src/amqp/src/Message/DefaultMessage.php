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

class DefaultMessage implements MessageInterface
{
    public $payload;

    public $type;

    public $exchange;

    public $routingKey;

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

    public function serialize(): string
    {
        return '';
    }

    public function unserialize(string $data)
    {
        return [];
    }

    public function getPoolName(): string
    {
        return 'default';
    }

    public function getExchangeDeclareBuilder(): ExchangeDeclareBuilder
    {
        // TODO: Implement getExchangeDeclareParams() method.
    }
}

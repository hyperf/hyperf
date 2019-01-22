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

use Hyperf\Amqp\ExchangeDeclareBuilder;

interface MessageInterface
{
    /**
     * Pool name for amqp.
     */
    public function getPoolName(): string;

    public function getType(): string;

    public function getExchange(): string;

    public function getRoutingKey(): string;

    public function getExchangeDeclareBuilder(): ExchangeDeclareBuilder;

    // $passive = false,
    // $durable = false,
    // $auto_delete = true,
    // $internal = false,
    // $nowait = false,
    // $arguments = array(),
    // $ticket = null

    /**
     * Serialize the message body to a string.
     */
    public function serialize(): string;

    /**
     * Unserialize the message body.
     */
    public function unserialize(string $data);
}

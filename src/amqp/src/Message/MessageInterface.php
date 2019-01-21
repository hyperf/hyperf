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

interface MessageInterface
{
    public function getPoolName(): string;

    /**
     * @return string Enum value of \Hyperf\Amqp\Message\Type
     */
    public function getType(): string;

    public function getExchange(): string;

    public function getRoutingKey(): string;

    /**
     * Serialize the message body to a string.
     */
    public function serialize(): string;

    /**
     * Unserialize the message body.
     */
    public function unserialize(string $data);
}

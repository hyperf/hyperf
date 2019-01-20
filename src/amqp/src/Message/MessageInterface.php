<?php

namespace Hyperf\Amqp\Message;


interface MessageInterface
{

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
    public function unserialize();

}
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

namespace Hyperf\Nsq;

class Message
{
    protected $timestamp;

    protected $attempts;

    protected $messageId;

    protected $body;

    public function __construct(string $payload)
    {
        $this->timestamp = Packer::unpackInt64(substr($payload, 0, 8));
        $this->attempts = Packer::unpackUInt16(substr($payload, 8, 2));
        $this->messageId = Packer::unpackString(substr($payload, 10, 16));
        $this->body = Packer::unpackString(substr($payload, 26));
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }

    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    public function getAttempts()
    {
        return $this->attempts;
    }

    public function setAttempts($attempts)
    {
        $this->attempts = $attempts;
        return $this;
    }

    public function getMessageId(): string
    {
        return $this->messageId;
    }

    public function setMessageId(string $messageId): Message
    {
        $this->messageId = $messageId;
        return $this;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): Message
    {
        $this->body = $body;
        return $this;
    }
}

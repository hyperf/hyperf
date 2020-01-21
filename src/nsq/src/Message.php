<?php

namespace Hyperf\Nsq;


class Message
{

    protected $timestamp;

    protected $attempts;

    protected $messageId;

    protected $body;

    public function __construct($decode)
    {
        $this->timestamp = unpack('q', substr($decode, 0, 8));
        $this->attempts = unpack('v', substr($decode, 8, 2));
        $this->messageId = $this->decodeString(substr($decode, 10, 16));
        $this->body = $this->decodeString(substr($decode, 26));
    }

    protected function decodeString($content)
    {
        $size = strlen($content);
        $bytes = unpack("c{$size}chars", $content);
        $string = '';
        foreach ($bytes as $byte) {
            $string .= chr($byte);
        }
        return $string;
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
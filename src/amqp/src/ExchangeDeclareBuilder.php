<?php

namespace Hyperf\Amqp;

use Hyperf\Amqp\Message\MessageInterface;

class ExchangeDeclareBuilder
{
    protected $exchange;

    protected $type;

    protected $passive = false;

    protected $durable = true;

    protected $autoDelete = true;

    protected $internal = false;

    protected $nowait = false;

    protected $arguments = [];

    protected $ticket = null;

    public function __construct(MessageInterface $message)
    {
        $this->setExchange($message->getExchange());
        $this->setType($message->getType());
    }

    /**
     * @return string
     */
    public function getExchange(): string
    {
        return $this->exchange;
    }

    /**
     * @param string $exchange
     * @return ExchangeDeclareBuilder
     */
    public function setExchange(string $exchange): ExchangeDeclareBuilder
    {
        $this->exchange = $exchange;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return ExchangeDeclareBuilder
     */
    public function setType(string $type): ExchangeDeclareBuilder
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPassive(): bool
    {
        return $this->passive;
    }

    /**
     * @param bool $passive
     * @return ExchangeDeclareBuilder
     */
    public function setPassive(bool $passive): ExchangeDeclareBuilder
    {
        $this->passive = $passive;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDurable(): bool
    {
        return $this->durable;
    }

    /**
     * @param bool $durable
     * @return ExchangeDeclareBuilder
     */
    public function setDurable(bool $durable): ExchangeDeclareBuilder
    {
        $this->durable = $durable;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAutoDelete(): bool
    {
        return $this->autoDelete;
    }

    /**
     * @param bool $autoDelete
     * @return ExchangeDeclareBuilder
     */
    public function setAutoDelete(bool $autoDelete): ExchangeDeclareBuilder
    {
        $this->autoDelete = $autoDelete;
        return $this;
    }

    /**
     * @return bool
     */
    public function isInternal(): bool
    {
        return $this->internal;
    }

    /**
     * @param bool $internal
     * @return ExchangeDeclareBuilder
     */
    public function setInternal(bool $internal): ExchangeDeclareBuilder
    {
        $this->internal = $internal;
        return $this;
    }

    /**
     * @return bool
     */
    public function isNowait(): bool
    {
        return $this->nowait;
    }

    /**
     * @param bool $nowait
     * @return ExchangeDeclareBuilder
     */
    public function setNowait(bool $nowait): ExchangeDeclareBuilder
    {
        $this->nowait = $nowait;
        return $this;
    }

    /**
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @param array $arguments
     * @return ExchangeDeclareBuilder
     */
    public function setArguments(array $arguments): ExchangeDeclareBuilder
    {
        $this->arguments = $arguments;
        return $this;
    }

    /**
     * @return null
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * @param null $ticket
     * @return ExchangeDeclareBuilder
     */
    public function setTicket($ticket)
    {
        $this->ticket = $ticket;
        return $this;
    }
}
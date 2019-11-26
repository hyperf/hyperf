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

namespace Hyperf\Amqp\Builder;

use PhpAmqpLib\Wire\AMQPTable;

class Builder
{
    protected $passive = false;

    protected $durable = true;

    protected $autoDelete = false;

    protected $nowait = false;

    protected $arguments = [];

    protected $ticket;

    public function isPassive(): bool
    {
        return $this->passive;
    }

    public function setPassive(bool $passive): self
    {
        $this->passive = $passive;
        return $this;
    }

    public function isDurable(): bool
    {
        return $this->durable;
    }

    public function setDurable(bool $durable): self
    {
        $this->durable = $durable;
        return $this;
    }

    public function isAutoDelete(): bool
    {
        return $this->autoDelete;
    }

    public function setAutoDelete(bool $autoDelete): self
    {
        $this->autoDelete = $autoDelete;
        return $this;
    }

    public function isNowait(): bool
    {
        return $this->nowait;
    }

    public function setNowait(bool $nowait): self
    {
        $this->nowait = $nowait;
        return $this;
    }

    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @param array|AMQPTable $arguments
     * @return $this
     */
    public function setArguments($arguments): self
    {
        $this->arguments = $arguments;
        return $this;
    }

    public function getTicket()
    {
        return $this->ticket;
    }

    public function setTicket($ticket): self
    {
        $this->ticket = $ticket;
        return $this;
    }
}

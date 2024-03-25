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

namespace Hyperf\Amqp\Builder;

use PhpAmqpLib\Wire\AMQPTable;

class Builder
{
    protected bool $passive = false;

    protected bool $durable = true;

    protected bool $autoDelete = false;

    protected bool $nowait = false;

    protected AMQPTable|array $arguments = [];

    protected ?int $ticket = null;

    public function isPassive(): bool
    {
        return $this->passive;
    }

    public function setPassive(bool $passive): static
    {
        $this->passive = $passive;
        return $this;
    }

    public function isDurable(): bool
    {
        return $this->durable;
    }

    public function setDurable(bool $durable): static
    {
        $this->durable = $durable;
        return $this;
    }

    public function isAutoDelete(): bool
    {
        return $this->autoDelete;
    }

    public function setAutoDelete(bool $autoDelete): static
    {
        $this->autoDelete = $autoDelete;
        return $this;
    }

    public function isNowait(): bool
    {
        return $this->nowait;
    }

    public function setNowait(bool $nowait): static
    {
        $this->nowait = $nowait;
        return $this;
    }

    public function getArguments(): AMQPTable|array
    {
        return $this->arguments;
    }

    public function setArguments(AMQPTable|array $arguments): static
    {
        $this->arguments = $arguments;
        return $this;
    }

    public function getTicket(): ?int
    {
        return $this->ticket;
    }

    public function setTicket(?int $ticket): static
    {
        $this->ticket = $ticket;
        return $this;
    }
}

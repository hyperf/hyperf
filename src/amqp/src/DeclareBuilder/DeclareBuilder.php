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

namespace Hyperf\Amqp\DeclareBuilder;

class DeclareBuilder
{
    protected $passive = false;

    protected $durable = true;

    protected $autoDelete = false;

    protected $nowait = false;

    protected $arguments = [];

    protected $ticket = null;

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
    public function setPassive(bool $passive): self
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
    public function setDurable(bool $durable): self
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
    public function setAutoDelete(bool $autoDelete): self
    {
        $this->autoDelete = $autoDelete;
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
    public function setNowait(bool $nowait): self
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
    public function setArguments(array $arguments): self
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

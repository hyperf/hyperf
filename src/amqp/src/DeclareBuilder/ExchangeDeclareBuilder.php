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

class ExchangeDeclareBuilder extends DeclareBuilder
{
    protected $exchange;

    protected $type;

    protected $internal = false;

    public function getExchange(): string
    {
        return $this->exchange;
    }

    public function setExchange(string $exchange): ExchangeDeclareBuilder
    {
        $this->exchange = $exchange;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): ExchangeDeclareBuilder
    {
        $this->type = $type;
        return $this;
    }

    public function isInternal(): bool
    {
        return $this->internal;
    }

    public function setInternal(bool $internal): ExchangeDeclareBuilder
    {
        $this->internal = $internal;
        return $this;
    }
}

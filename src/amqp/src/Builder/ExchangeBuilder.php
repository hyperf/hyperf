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

use Hyperf\Amqp\Message\Type;

class ExchangeBuilder extends Builder
{
    protected ?string $exchange = null;

    protected null|string|Type $type = null;

    protected bool $internal = false;

    public function getExchange(): string
    {
        return $this->exchange;
    }

    public function setExchange(string $exchange): static
    {
        $this->exchange = $exchange;
        return $this;
    }

    public function getType(): string|Type
    {
        return $this->type;
    }

    public function getTypeString(): string
    {
        return $this->type instanceof Type ? $this->type->value : $this->type;
    }

    public function setType(string|Type $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function isInternal(): bool
    {
        return $this->internal;
    }

    public function setInternal(bool $internal): static
    {
        $this->internal = $internal;
        return $this;
    }
}

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
}

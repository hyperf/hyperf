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

use Hyperf\Amqp\Message\MessageInterface;

class ExchangeDeclareBuilder extends DeclareBuilder
{
    protected $exchange;

    protected $type;

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
}

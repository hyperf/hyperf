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
namespace Hyperf\Amqp\Event;

use Hyperf\Amqp\Message\ConsumerMessageInterface;

class AfterConsume extends ConsumeEvent
{
    protected $result;

    public function __construct(ConsumerMessageInterface $message, string $result)
    {
        parent::__construct($message);
        $this->result = $result;
    }

    public function getResult(): string
    {
        return $this->result;
    }
}

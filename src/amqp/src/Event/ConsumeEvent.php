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

class ConsumeEvent
{
    /**
     * @var ConsumerMessageInterface
     */
    protected $message;

    public function __construct(ConsumerMessageInterface $message)
    {
        $this->message = $message;
    }

    public function getMessage(): ConsumerMessageInterface
    {
        return $this->message;
    }
}

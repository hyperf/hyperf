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
    public function __construct(protected ConsumerMessageInterface $message)
    {
    }

    public function getMessage(): ConsumerMessageInterface
    {
        return $this->message;
    }
}

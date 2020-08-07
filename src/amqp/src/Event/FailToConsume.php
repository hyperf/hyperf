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
use Throwable;

class FailToConsume extends ConsumeEvent
{
    /**
     * @var Throwable
     */
    protected $throwable;

    public function __construct(ConsumerMessageInterface $message, Throwable $throwable)
    {
        parent::__construct($message);
        $this->throwable = $throwable;
    }

    public function getThrowable(): Throwable
    {
        return $this->throwable;
    }
}

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

namespace Hyperf\Kafka;

class Result
{
    /**
     * Acknowledge the message.
     */
    public const ACK = 'ack';

    /**
     * Reject the message and requeue it.
     */
    public const REQUEUE = 'requeue';

    /**
     * Reject the message and drop it.
     */
    public const DROP = 'drop';
}

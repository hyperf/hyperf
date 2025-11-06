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

namespace Hyperf\AsyncQueue;

enum Result: string
{
    /**
     * Acknowledge the message.
     */
    case ACK = 'ack';

    /**
     * Reject the message and requeue it.
     */
    case REQUEUE = 'requeue';

    /**
     * Retry the message.
     */
    case RETRY = 'retry';

    /**
     * Reject the message and drop it.
     */
    case DROP = 'drop';
}

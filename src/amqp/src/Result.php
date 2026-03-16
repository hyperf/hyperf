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

namespace Hyperf\Amqp;

enum Result: string
{
    /*
     * Acknowledge the message.
     */
    case ACK = 'ack';

    /*
     * Unacknowledged the message.
     */
    case NACK = 'nack';

    /*
     * Reject the message and requeue it.
     */
    case REQUEUE = 'requeue';

    /*
     * Reject the message and drop it.
     */
    case DROP = 'drop';
}

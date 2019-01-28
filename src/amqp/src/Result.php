<?php

namespace Hyperf\Amqp;


class Result
{

    /**
     * Acknowledge the message.
     */
    const ACK = 'ack';

    /**
     * Reject the message and requeue it.
     */
    const REQUEUE = 'requeue';

    /**
     * Reject the message and drop it.
     */
    const DROP = 'drop';

}
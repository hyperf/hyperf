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

namespace Hyperf\Nats;

use Closure;
use Hyperf\Nats\Encoders\Encoder;

/**
 * Class EncodedConnection.
 */
class EncodedConnection extends Connection
{
    /**
     * EncodedConnection constructor.
     *
     * @param ConnectionOptions $options connection options object
     * @param Encoder $encoder encoder to use with the payload
     */
    public function __construct(ConnectionOptions $options, private Encoder $encoder)
    {
        parent::__construct($options);
    }

    /**
     * Publish publishes the data argument to the given subject.
     *
     * @param string $subject message topic
     * @param string $payload message data
     * @param string $inbox message inbox
     */
    public function publish(string $subject, mixed $payload = null, ?string $inbox = null): void
    {
        $payload = $this->encoder->encode($payload);
        parent::publish($subject, $payload, $inbox);
    }

    /**
     * Subscribes to a specific event given a subject.
     *
     * @param string $subject message topic
     * @param Closure $callback closure to be executed as callback
     */
    public function subscribe(string $subject, Closure $callback): string
    {
        $c = function ($message) use ($callback) {
            $message->setBody($this->encoder->decode($message->getBody()));
            $callback($message);
        };
        return parent::subscribe($subject, $c);
    }

    /**
     * Subscribes to an specific event given a subject and a queue.
     *
     * @param string $subject message topic
     * @param string $queue queue name
     * @param Closure $callback closure to be executed as callback
     */
    public function queueSubscribe(string $subject, string $queue, Closure $callback): string
    {
        $c = function ($message) use ($callback) {
            $message->setBody($this->encoder->decode($message->getBody()));
            $callback($message);
        };
        return parent::queueSubscribe($subject, $queue, $c);
    }
}

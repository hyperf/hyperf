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

use Hyperf\Nats\Encoders\Encoder;

/**
 * Class EncodedConnection.
 */
class EncodedConnection extends Connection
{
    /**
     * Encoder for this connection.
     *
     * @var null|Encoder
     */
    private $encoder;

    /**
     * EncodedConnection constructor.
     *
     * @param ConnectionOptions $options connection options object
     * @param null|Encoder $encoder encoder to use with the payload
     */
    public function __construct(ConnectionOptions $options = null, Encoder $encoder = null)
    {
        $this->encoder = $encoder;
        parent::__construct($options);
    }

    /**
     * Publish publishes the data argument to the given subject.
     *
     * @param string $subject message topic
     * @param string $payload message data
     * @param string $inbox message inbox
     */
    public function publish($subject, $payload = null, $inbox = null)
    {
        $payload = $this->encoder->encode($payload);
        parent::publish($subject, $payload, $inbox);
    }

    /**
     * Subscribes to an specific event given a subject.
     *
     * @param string $subject message topic
     * @param \Closure $callback closure to be executed as callback
     *
     * @return string
     */
    public function subscribe($subject, \Closure $callback)
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
     * @param \Closure $callback closure to be executed as callback
     */
    public function queueSubscribe($subject, $queue, \Closure $callback)
    {
        $c = function ($message) use ($callback) {
            $message->setBody($this->encoder->decode($message->getBody()));
            $callback($message);
        };
        return parent::queueSubscribe($subject, $queue, $c);
    }
}

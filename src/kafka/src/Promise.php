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

use Hyperf\Engine\Channel;
use Hyperf\Kafka\Exception\TimeoutException;
use Throwable;

/**
 * @mixin Channel
 */
class Promise
{
    private Channel $chan;

    public function __construct(private int $timeout = 10)
    {
        $this->chan = new Channel(1);
    }

    public function __destruct()
    {
        try {
            $this->chan->close();
        } catch (Throwable) {
        }
    }

    public function __call($name, $arguments)
    {
        return $this->chan->{$name}(...$arguments);
    }

    /**
     * @throws Throwable
     * @throws TimeoutException
     */
    public function wait(): void
    {
        if ($e = $this->chan->pop($this->timeout)) {
            throw $e;
        }

        if ($this->chan->isTimeout()) {
            throw new TimeoutException('Kafka send timeout.');
        }
    }
}

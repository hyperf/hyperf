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
namespace Hyperf\Coroutine\Channel;

use Closure;
use Hyperf\Coroutine\Exception\ChannelClosedException;
use Hyperf\Coroutine\Exception\WaitTimeoutException;
use Hyperf\Engine\Channel;

class Caller
{
    protected ?Channel $channel = null;

    public function __construct(protected Closure $closure, protected float $waitTimeout = 10)
    {
        $this->initInstance();
    }

    public function call(Closure $closure)
    {
        $release = true;
        $channel = $this->channel;
        try {
            $instance = $channel->pop($this->waitTimeout);
            if ($instance === false) {
                if ($channel->isClosing()) {
                    throw new ChannelClosedException('The channel was closed.');
                }

                if ($channel->isTimeout()) {
                    throw new WaitTimeoutException('The instance pop from channel timeout.');
                }
            }

            $result = $closure($instance);
        } catch (ChannelClosedException|WaitTimeoutException $exception) {
            $release = false;
            throw $exception;
        } finally {
            $release && $channel->push($instance ?? null);
        }

        return $result;
    }

    public function initInstance(): void
    {
        $this->channel?->close();
        $this->channel = new Channel(1);
        $this->channel->push($this->closure->__invoke());
    }
}

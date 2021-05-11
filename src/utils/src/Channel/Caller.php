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
namespace Hyperf\Utils\Channel;

use Closure;
use Hyperf\Engine\Channel;
use Hyperf\Utils\Exception\ChannelClosedException;
use Hyperf\Utils\Exception\WaitTimeoutException;

class Caller
{
    /**
     * @var null|Channel
     */
    protected $channel;

    /**
     * @var float wait seconds
     */
    protected $waitTimeout;

    /**
     * @var null|Closure
     */
    protected $closure;

    public function __construct(Closure $closure, float $waitTimeout = 10)
    {
        $this->closure = $closure;
        $this->waitTimeout = $waitTimeout;
        $this->initInstance();
    }

    public function call(Closure $closure)
    {
        $release = true;
        try {
            $channel = $this->channel;
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
        } catch (ChannelClosedException | WaitTimeoutException $exception) {
            $release = false;
            throw $exception;
        } finally {
            $release && $channel->push($instance ?? null);
        }

        return $result;
    }

    public function initInstance(): void
    {
        if ($this->channel) {
            $this->channel->close();
        }

        $this->channel = new Channel(1);
        $this->channel->push($this->closure->__invoke());
    }
}

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

namespace Hyperf\SingleFlight;

use Hyperf\Engine\Channel;
use Hyperf\SingleFlight\Exception\ForgetException;
use Hyperf\SingleFlight\Exception\RuntimeException;
use Hyperf\SingleFlight\Exception\SingleFlightException;
use Hyperf\SingleFlight\Exception\TimeoutException;
use Throwable;

use function Hyperf\Support\call;

class Caller
{
    private mixed $result = null;

    private bool $done = false;

    private bool $forgotten = false;

    private ?Channel $channel;

    public function __construct(
        protected string $barrierKey,
    ) {
        $this->channel = new Channel(1);
    }

    /**
     * @throws RuntimeException
     */
    public function share(callable $processor): mixed
    {
        try {
            $this->result = call($processor);
            $this->done = true;
            $this->resumeWaiters();

            return $this->result;
        } catch (Throwable $th) {
            $this->result = new RuntimeException(message: "An exception occurred while sharing the result on {$this->barrierKey}", previous: $th);
            $this->done = true;
            $this->resumeWaiters();

            throw $this->result;
        }
    }

    /**
     * @throws SingleFlightException
     */
    public function wait(float $timeout = -1): mixed
    {
        if ($this->done) {
            if ($this->result instanceof SingleFlightException) {
                throw $this->result;
            }
            return $this->result;
        }

        $ret = $this->channel->pop($timeout);

        if ($ret === false && $this->channel->isTimeout()) {
            throw new TimeoutException(message: "Exceeded maximum waiting time for result on {$this->barrierKey}");
        }

        if ($this->forgotten) {
            throw $this->result;
        }

        if ($this->result instanceof RuntimeException) {
            throw new RuntimeException(message: "An exception occurred while waiting for the shared result on {$this->barrierKey}", previous: $this->result->getPrevious());
        }

        return $this->result;
    }

    public function forget(): void
    {
        if ($this->forgotten) {
            return;
        }

        $this->forgotten = true;
        $this->result = new ForgetException("SingleFlight {$this->barrierKey} has been forgotten while waiting for the result");
        $this->done = true;

        $this->resumeWaiters();
    }

    public function waiters(): int
    {
        return $this->channel->stats()['consumer_num'];
    }

    public function isForgotten(): bool
    {
        return $this->forgotten;
    }

    private function resumeWaiters(): void
    {
        $this->channel->close();
    }
}

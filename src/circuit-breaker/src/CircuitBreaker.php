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

namespace Hyperf\CircuitBreaker;

use Psr\Container\ContainerInterface;

use function Hyperf\Support\make;

class CircuitBreaker implements CircuitBreakerInterface
{
    protected string $name;

    protected State $state;

    protected float $timestamp;

    /**
     * Failure count.
     */
    protected int $failCounter;

    /**
     * Success count.
     */
    protected int $successCounter;

    public function __construct(protected ContainerInterface $container, string $name)
    {
        $this->name = $name;
        $this->state = make(State::class);
        $this->init();
    }

    public function state(): State
    {
        return $this->state;
    }

    public function attempt(): bool
    {
        return $this->container->get(Attempt::class)->attempt();
    }

    public function open(): void
    {
        $this->init();
        $this->state->open();
    }

    public function close(): void
    {
        $this->init();
        $this->state->close();
    }

    public function halfOpen(): void
    {
        $this->init();
        $this->state->halfOpen();
    }

    public function getDuration(): float
    {
        return microtime(true) - $this->timestamp;
    }

    public function getFailCounter(): int
    {
        return $this->failCounter;
    }

    public function getSuccessCounter(): int
    {
        return $this->successCounter;
    }

    public function getTimestamp(): float
    {
        return $this->timestamp;
    }

    public function incrFailCounter(): int
    {
        return ++$this->failCounter;
    }

    public function incrSuccessCounter(): int
    {
        return ++$this->successCounter;
    }

    protected function init()
    {
        $this->timestamp = microtime(true);
        $this->failCounter = 0;
        $this->successCounter = 0;
    }
}

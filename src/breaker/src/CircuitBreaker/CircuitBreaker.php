<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Breaker\CircuitBreaker;

use Hyperf\Breaker\Attempt;
use Hyperf\Breaker\State;
use Psr\Container\ContainerInterface;

class CircuitBreaker implements CircuitBreakerInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var State
     */
    protected $state;

    /**
     * Current state duration.
     * @var float
     */
    protected $duration;

    /**
     * Failure count.
     * @var int
     */
    protected $failCounter;

    /**
     * Success count.
     * @var int
     */
    protected $successCounter;

    public function __construct(ContainerInterface $container, string $name)
    {
        $this->container = $container;
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
        /** @var Attempt $attempt */
        $attempt = $this->container->get(Attempt::class);
        return $attempt->attempt();
    }

    public function open()
    {
        $this->init();
        $this->state->open();
    }

    public function close()
    {
        $this->init();
        $this->state->close();
    }

    public function halfOpen()
    {
        $this->init();
        $this->state->halfOpen();
    }

    /**
     * @return float
     */
    public function getDuration(): float
    {
        return $this->duration;
    }

    /**
     * @return int
     */
    public function getFailCounter(): int
    {
        return $this->failCounter;
    }

    /**
     * @return int
     */
    public function getSuccessCounter(): int
    {
        return $this->successCounter;
    }

    public function incFailCounter(): int
    {
        return ++$this->failCounter;
    }

    public function incSuccessCounter(): int
    {
        return ++$this->successCounter;
    }

    protected function init()
    {
        $this->duration = microtime(true);
        $this->failCounter = 0;
        $this->successCounter = 0;
    }
}

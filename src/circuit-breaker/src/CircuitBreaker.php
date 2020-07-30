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
     * @var float
     */
    protected $timestamp;

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
        $this->timestamp = microtime(true);
        $this->failCounter = 0;
        $this->successCounter = 0;
    }
}

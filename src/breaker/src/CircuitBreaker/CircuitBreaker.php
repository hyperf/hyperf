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

use Hyperf\Breaker\State;

class CircuitBreaker implements CircuitBreakerInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var State
     */
    protected $state;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->state = make(State::class);
    }

    public function state(): State
    {
        return $this->state;
    }
}

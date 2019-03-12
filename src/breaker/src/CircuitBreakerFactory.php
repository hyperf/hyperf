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

namespace Hyperf\Breaker;

use Hyperf\Breaker\CircuitBreaker\CircuitBreakerInterface;
use Psr\Container\ContainerInterface;

class CircuitBreakerFactory
{
    protected $container;

    protected $breakers = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function get(string $name): ?CircuitBreakerInterface
    {
        return $this->breakers[$name] ?? null;
    }

    public function has(string $name): bool
    {
        return isset($this->breakers[$name]);
    }

    public function set(string $name, CircuitBreakerInterface $storage): CircuitBreakerInterface
    {
        return $this->breakers[$name] = $storage;
    }
}

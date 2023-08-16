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

class CircuitBreakerFactory
{
    /**
     * @var CircuitBreakerInterface[]
     */
    protected array $breakers = [];

    public function __construct(protected ContainerInterface $container)
    {
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

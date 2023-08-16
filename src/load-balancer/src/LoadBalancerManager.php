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
namespace Hyperf\LoadBalancer;

use InvalidArgumentException;

use function Hyperf\Support\make;

class LoadBalancerManager
{
    private array $algorithms = [
        'random' => Random::class,
        'round-robin' => RoundRobin::class,
        'weighted-random' => WeightedRandom::class,
        'weighted-round-robin' => WeightedRoundRobin::class,
    ];

    /**
     * @var LoadBalancerInterface[]
     */
    private array $instances = [];

    /**
     * Retrieve a class name of load balancer.
     */
    public function get(string $name): string
    {
        if (! $this->has($name)) {
            throw new InvalidArgumentException(sprintf('The %s algorithm does not exists.', $name));
        }
        return $this->algorithms[$name];
    }

    /**
     * Retrieve a class name of load balancer and create an object instance,
     * If $container object exists, then the class will create via container.
     *
     * @param string $key key of the load balancer instance
     * @param string $algorithm The name of the load balance algorithm
     */
    public function getInstance(string $key, string $algorithm): LoadBalancerInterface
    {
        if (isset($this->instances[$key])) {
            return $this->instances[$key];
        }
        $class = $this->get($algorithm);
        if (function_exists('make')) {
            $instance = make($class);
        } else {
            $instance = new $class();
        }
        $this->instances[$key] = $instance;
        return $instance;
    }

    /**
     * Determine if the algorithm is exists.
     */
    public function has(string $name): bool
    {
        return isset($this->algorithms[$name]);
    }

    /**
     * Override the algorithms.
     */
    public function set(array $algorithms): static
    {
        foreach ($algorithms as $algorithm) {
            if (! class_exists($algorithm)) {
                throw new InvalidArgumentException(sprintf('The class of %s algorithm does not exists.', $algorithm));
            }
        }
        $this->algorithms = $algorithms;
        return $this;
    }

    /**
     * Register an algorithm to the manager.
     */
    public function register(string $key, string $algorithm): self
    {
        if (! class_exists($algorithm)) {
            throw new InvalidArgumentException(sprintf('The class of %s algorithm does not exists.', $algorithm));
        }
        $this->algorithms[$key] = $algorithm;
        return $this;
    }
}

<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\LoadBalancer;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;

class LoadBalancerManager
{
    /**
     * @var null|ContainerInterface
     */
    private $container;

    /**
     * @var array
     */
    private $algorithms = [
        'random' => Random::class,
        'round-robin' => RoundRobin::class,
        'weighted-random' => WeightedRandom::class,
        'weighted-round-robin' => WeightedRoundRobin::class,
    ];

    public function __construct(?ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Retrieve a class name of load balancer.
     */
    public function get(string $name): string
    {
        if (! $this->has($name)) {
            throw new InvalidArgumentException(sprintf('%s algorithm class not exists.', $name));
        }
        return $this->algorithms[$name];
    }

    /**
     * Retrieve a class name of load balancer and create a object instance,
     * If $container object exists, then the class will create via container.
     */
    public function getInstance(string $name): LoadBalancerInterface
    {
        $class = $this->get($name);
        if ($this->container) {
            return $this->container->get($class);
        }
        return new $class();
    }

    /**
     * Determire if the algorithm is exists.
     */
    public function has(string $name): bool
    {
        return isset($this->algorithms[$name]);
    }

    /**
     * Override the algorithms.
     */
    public function set(array $algorithms): self
    {
        foreach ($algorithms as $algorithm) {
            if (! class_exists($algorithm)) {
                throw new InvalidArgumentException(sprintf('%s algorithm class not exists.', $algorithm));
            }
        }
        $this->algorithms = $algorithms;
        return $this;
    }

    /**
     * Register a algorithm to the manager.
     */
    public function register(string $key, string $algorithm): self
    {
        if (! class_exists($algorithm)) {
            throw new InvalidArgumentException(sprintf('%s algorithm class not exists.', $algorithm));
        }
        $this->algorithms[$key] = $algorithm;
        return $this;
    }
}

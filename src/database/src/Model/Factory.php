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

namespace Hyperf\Database\Model;

use ArrayAccess;
use Faker\Generator as Faker;
use Symfony\Component\Finder\Finder;

class Factory implements ArrayAccess
{
    /**
     * The model definitions in the container.
     */
    protected array $definitions = [];

    /**
     * The registered model states.
     */
    protected array $states = [];

    /**
     * The registered after making callbacks.
     */
    protected array $afterMaking = [];

    /**
     * The registered after creating callbacks.
     */
    protected array $afterCreating = [];

    /**
     * Create a new factory instance.
     */
    public function __construct(protected Faker $faker)
    {
    }

    /**
     * Create a new factory container.
     */
    public static function construct(Faker $faker, string $pathToFactories = BASE_PATH . '/database/factories'): static
    {
        return (new static($faker))->load($pathToFactories);
    }

    /**
     * Define a class with a given short-name.
     *
     * @return $this
     */
    public function defineAs(string $class, string $name, callable $attributes)
    {
        return $this->define($class, $attributes, $name);
    }

    /**
     * Define a class with a given set of attributes.
     *
     * @return $this
     */
    public function define(string $class, callable $attributes, string $name = 'default')
    {
        $this->definitions[$class][$name] = $attributes;

        return $this;
    }

    /**
     * Define a state with a given set of attributes.
     *
     * @return $this
     */
    public function state(string $class, string $state, array|callable $attributes)
    {
        $this->states[$class][$state] = $attributes;

        return $this;
    }

    /**
     * Define a callback to run after making a model.
     *
     * @return $this
     */
    public function afterMaking(string $class, callable $callback, string $name = 'default')
    {
        $this->afterMaking[$class][$name][] = $callback;

        return $this;
    }

    /**
     * Define a callback to run after making a model with given state.
     *
     * @return $this
     */
    public function afterMakingState(string $class, string $state, callable $callback)
    {
        return $this->afterMaking($class, $callback, $state);
    }

    /**
     * Define a callback to run after creating a model.
     *
     * @return $this
     */
    public function afterCreating(string $class, callable $callback, string $name = 'default')
    {
        $this->afterCreating[$class][$name][] = $callback;

        return $this;
    }

    /**
     * Define a callback to run after creating a model with given state.
     *
     * @return $this
     */
    public function afterCreatingState(string $class, string $state, callable $callback)
    {
        return $this->afterCreating($class, $callback, $state);
    }

    /**
     * Create an instance of the given model and persist it to the database.
     */
    public function create(string $class, array $attributes = [])
    {
        return $this->of($class)->create($attributes);
    }

    /**
     * Create an instance of the given model and type and persist it to the database.
     */
    public function createAs(string $class, string $name, array $attributes = [])
    {
        return $this->of($class, $name)->create($attributes);
    }

    /**
     * Create an instance of the given model.
     */
    public function make(string $class, array $attributes = [])
    {
        return $this->of($class)->make($attributes);
    }

    /**
     * Create an instance of the given model and type.
     */
    public function makeAs(string $class, string $name, array $attributes = [])
    {
        return $this->of($class, $name)->make($attributes);
    }

    /**
     * Get the raw attribute array for a given named model.
     */
    public function rawOf(string $class, string $name, array $attributes = []): array
    {
        return $this->raw($class, $attributes, $name);
    }

    /**
     * Get the raw attribute array for a given model.
     */
    public function raw(string $class, array $attributes = [], string $name = 'default'): array
    {
        return array_merge(
            call_user_func($this->definitions[$class][$name], $this->faker),
            $attributes
        );
    }

    /**
     * Create a builder for the given model.
     *
     * @return FactoryBuilder
     */
    public function of(string $class, string $name = 'default')
    {
        return new FactoryBuilder(
            $class,
            $name,
            $this->definitions,
            $this->states,
            $this->afterMaking,
            $this->afterCreating,
            $this->faker
        );
    }

    /**
     * Load factories from path.
     *
     * @return $this
     */
    public function load(string $path)
    {
        $factory = $this;

        if (is_dir($path)) {
            foreach (Finder::create()->files()->name('*.php')->in($path) as $file) {
                require $file->getRealPath();
            }
        }

        return $factory;
    }

    /**
     * Determine if the given offset exists.
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->definitions[$offset]);
    }

    /**
     * Get the value of the given offset.
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->make($offset);
    }

    /**
     * Set the given offset to the given value.
     *
     * @param string $offset
     * @param callable $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->define($offset, $value);
    }

    /**
     * Unset the value at the given offset.
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->definitions[$offset]);
    }
}

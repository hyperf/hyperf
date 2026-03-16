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

namespace Hyperf\Testing;

use Faker\Generator;
use Hyperf\Database\Model\Factory;

/**
 * @template TClass
 */
class ModelFactory
{
    public function __construct(protected Factory $factory)
    {
    }

    public static function create(Generator $faker)
    {
        return new static(new Factory($faker));
    }

    public function define(string $class, callable $attributes, string $name = 'default'): void
    {
        $this->factory->define(...func_get_args());
    }

    /**
     * @param class-string<TClass> $class
     * @return TClass
     */
    public function factory(string $class)
    {
        $arguments = func_get_args();

        if (isset($arguments[1]) && is_string($arguments[1])) {
            return $this->factory->of($arguments[0], $arguments[1])->times($arguments[2] ?? null);
        }
        if (isset($arguments[1])) {
            return $this->factory->of($arguments[0])->times($arguments[1]);
        }

        return $this->factory->of($arguments[0]);
    }

    public function load(string $path): void
    {
        $this->factory->load($path);
    }
}

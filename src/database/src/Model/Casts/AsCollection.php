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

namespace Hyperf\Database\Model\Casts;

use Hyperf\Collection\Collection;
use Hyperf\Contract\CastsAttributes;
use Hyperf\Stringable\Str;
use InvalidArgumentException;

class AsCollection implements CastsAttributes
{
    public function __construct(protected ?string $collectionClass = null, protected ?string $parseCallback = null)
    {
    }

    public function get($model, string $key, $value, array $attributes)
    {
        if (! isset($attributes[$key])) {
            return null;
        }

        $data = Json::decode($attributes[$key]);

        $collectionClass = empty($this->collectionClass) ? Collection::class : $this->collectionClass;

        if (! is_a($collectionClass, Collection::class, true)) {
            throw new InvalidArgumentException('The provided class must extend [' . Collection::class . '].');
        }

        if (! is_array($data)) {
            return null;
        }

        $instance = new $collectionClass($data);

        if (empty($this->parseCallback)) {
            return $instance;
        }

        $parseCallback = Str::parseCallback($this->parseCallback);
        if (is_callable($parseCallback)) {
            return $instance->map($parseCallback);
        }

        return $instance->mapInto($parseCallback[0]);
    }

    public function set($model, $key, $value, $attributes)
    {
        return [$key => Json::encode($value)];
    }

    /**
     * Specify the type of object each item in the collection should be mapped to.
     *
     * @param array{class-string, string}|class-string $map
     * @return string
     */
    public static function of($map)
    {
        return static::using('', $map);
    }

    /**
     * Specify the collection type for the cast.
     *
     * @param class-string $class
     * @param array{class-string, string}|class-string $map
     * @return string
     */
    public static function using($class, $map = null)
    {
        if (
            is_array($map)
            && count($map) === 2
            && is_string($map[0])
            && is_string($map[1])
            && is_callable($map)
        ) {
            $map = $map[0] . '@' . $map[1];
        }

        return static::class . ':' . implode(',', [$class, $map]);
    }
}

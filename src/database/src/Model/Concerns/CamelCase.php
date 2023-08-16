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
namespace Hyperf\Database\Model\Concerns;

use Hyperf\Stringable\Str;

trait CamelCase
{
    public function getAttribute($key)
    {
        return parent::getAttribute($key) ?? parent::getAttribute(Str::snake($key));
    }

    public function setAttribute($key, $value)
    {
        return parent::setAttribute(Str::snake($key), $value);
    }

    public function jsonSerialize(): mixed
    {
        $array = [];
        foreach ($this->toArray() as $key => $value) {
            $array[$this->keyTransform($key)] = $value;
        }
        return $array;
    }

    public function getFillable(): array
    {
        $fillable = [];
        foreach (parent::getFillable() as $key) {
            $fillable[] = $this->keyTransform($key);
        }
        return $fillable;
    }

    public function toArray(): array
    {
        $array = [];
        foreach (parent::toArray() as $key => $value) {
            $array[$this->keyTransform($key)] = $value;
        }
        return $array;
    }

    public function toOriginalArray(): array
    {
        return parent::toArray();
    }

    protected function keyTransform($key)
    {
        return Str::camel($key);
    }
}

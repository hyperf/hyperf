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

use Hyperf\Utils\Str;

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

    public function jsonSerialize()
    {
        $array = [];
        foreach ($this->toArray() as $key => $value) {
            $array[$this->keyTransform($key)] = $value;
        }
        return $array;
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

    protected function addMutatedAttributesToArray(array $attributes, array $mutatedAttributes)
    {
        foreach ($mutatedAttributes as $key) {
            if (! array_key_exists($this->keyTransform($key), $attributes)) {
                continue;
            }
            $attributes[$this->keyTransform($key)] = $this->mutateAttributeForArray(
                $this->keyTransform($key),
                $attributes[$this->keyTransform($key)]
            );
        }
        return $attributes;
    }
}

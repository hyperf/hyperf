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

namespace Hyperf\Rpn\Operator;

use Hyperf\Rpn\Exception\InvalidValueException;

trait HasBindings
{
    public function fromBindings(array $parameters, array $bindings): array
    {
        $result = [];
        foreach ($parameters as $parameter) {
            if ($this->isBinding($parameter)) {
                $index = $this->getBindingIndex($parameter);
                $value = $bindings[$index] ?? null;
                if ($value === null) {
                    throw new InvalidValueException(sprintf('The value of index %d is not found.', $index));
                }

                $result[] = (string) $value;
                continue;
            }

            $result[] = (string) $parameter;
        }

        return $result;
    }

    protected function getBindingIndex(string $tag): int
    {
        return (int) substr($tag, 1, -1);
    }

    protected function isBinding(string $tag): bool
    {
        return str_starts_with($tag, '[') && str_ends_with($tag, ']');
    }
}

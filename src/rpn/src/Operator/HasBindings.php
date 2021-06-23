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
    public function fromBindings(array $paramaters, array $bindings): array
    {
        $result = [];
        foreach ($paramaters as $paramater) {
            if ($this->isBinding($paramater)) {
                $index = $this->getBindingIndex($paramater);
                $value = $bindings[$index] ?? null;
                if ($value === null) {
                    throw new InvalidValueException(sprintf('The value of index %d is not found.', $index));
                }

                $result[] = (string) $value;
                continue;
            }

            $result[] = (string) $paramater;
        }

        return $result;
    }

    protected function getBindingIndex(string $tag): int
    {
        return (int) substr($tag, 1, -1);
    }

    protected function isBinding(string $tag): bool
    {
        return substr($tag, 0, 1) === '['
            && substr($tag, -1) === ']';
    }
}

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

namespace Hyperf\Validation\Rules;

use Stringable;

class RequiredIf implements Stringable
{
    /**
     * The condition that validates the attribute.
     *
     * @var bool|callable
     */
    public mixed $condition;

    /**
     * Create a new required validation rule based on a condition.
     */
    public function __construct(bool|callable $condition)
    {
        $this->condition = $condition;
    }

    /**
     * Convert the rule to a validation string.
     */
    public function __toString(): string
    {
        if (is_callable($this->condition)) {
            return call_user_func($this->condition) ? 'required' : '';
        }

        return $this->condition ? 'required' : '';
    }
}

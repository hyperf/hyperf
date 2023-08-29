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

use Closure;
use InvalidArgumentException;

class ProhibitedIf
{
    public $condition;

    public function __construct($condition)
    {
        if ($condition instanceof Closure || is_bool($condition)) {
            $this->condition = $condition;
        } else {
            throw new InvalidArgumentException('The provided condition must be a callable or boolean.');
        }
    }

    public function __toString()
    {
        if (is_callable($this->condition)) {
            return call_user_func($this->condition) ? 'prohibited' : '';
        }

        return $this->condition ? 'prohibited' : '';
    }
}
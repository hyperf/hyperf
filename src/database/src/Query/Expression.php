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
namespace Hyperf\Database\Query;

class Expression
{
    /**
     * Create a new raw query expression.
     * @param mixed $value the value of the expression
     */
    public function __construct(protected mixed $value)
    {
    }

    /**
     * Get the value of the expression.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getValue();
    }

    /**
     * Get the value of the expression.
     */
    public function getValue()
    {
        return $this->value;
    }
}

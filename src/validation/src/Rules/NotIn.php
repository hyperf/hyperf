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

class NotIn
{
    /**
     * The name of the rule.
     */
    protected $rule = 'not_in';

    /**
     * The accepted values.
     *
     * @var array
     */
    protected $values;

    /**
     * Create a new "not in" rule instance.
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * Convert the rule to a validation string.
     */
    public function __toString(): string
    {
        $values = array_map(function ($value) {
            return '"' . str_replace('"', '""', $value) . '"';
        }, $this->values);

        return $this->rule . ':' . implode(',', $values);
    }
}

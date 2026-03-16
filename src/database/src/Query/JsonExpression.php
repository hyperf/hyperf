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

use InvalidArgumentException;

class JsonExpression extends Expression
{
    /**
     * Create a new raw query expression.
     */
    public function __construct(mixed $value)
    {
        parent::__construct(
            $this->getJsonBindingParameter($value)
        );
    }

    /**
     * Translate the given value into the appropriate JSON binding parameter.
     *
     * @return string
     * @throws InvalidArgumentException
     */
    protected function getJsonBindingParameter(mixed $value)
    {
        if ($value instanceof Expression) {
            return $value->getValue();
        }

        $type = gettype($value);
        return match ($type) {
            'boolean' => $value ? 'true' : 'false',
            'NULL', 'integer', 'double', 'string', 'object', 'array' => '?',
            default => throw new InvalidArgumentException("JSON value is of illegal type: {$type}")
        };
    }
}

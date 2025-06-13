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

use Hyperf\Validation\ValidationRuleParser;
use Stringable;

class In implements Stringable
{
    /**
     * The name of the rule.
     */
    protected string $rule = 'in';

    /**
     * Create a new in rule instance.
     *
     * @param array $values the accepted values
     */
    public function __construct(protected array $values)
    {
    }

    /**
     * Convert the rule to a validation string.
     *
     * @see ValidationRuleParser::parseParameters
     */
    public function __toString(): string
    {
        $values = array_map(fn ($value) => '"' . str_replace('"', '""', (string) $value) . '"', $this->values);

        return $this->rule . ':' . implode(',', $values);
    }
}

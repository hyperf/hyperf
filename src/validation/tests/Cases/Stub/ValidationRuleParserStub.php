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

namespace HyperfTest\Validation\Cases\Stub;

use Hyperf\Validation\ValidationRuleParser;

class ValidationRuleParserStub extends ValidationRuleParser
{
    public static function parseParameters(string $rule, string $parameter): array
    {
        return parent::parseParameters($rule, $parameter);
    }
}

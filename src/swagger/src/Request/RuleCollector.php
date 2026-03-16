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

namespace Hyperf\Swagger\Request;

/**
 * @deprecated will remove, please use Hyperf\Swagger\Request\ValidationCollector instead
 */
class RuleCollector
{
    public static function get(string $class, string $method): array
    {
        return ValidationCollector::get($class, $method, 'rules');
    }
}

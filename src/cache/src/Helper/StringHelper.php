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

namespace Hyperf\Cache\Helper;

use Hyperf\Stringable\Str;

use function Hyperf\Collection\data_get;

class StringHelper
{
    /**
     * Format cache key with prefix and arguments.
     */
    public static function format(string $prefix, array $arguments, ?string $value = null): string
    {
        if ($value !== null) {
            if ($matches = StringHelper::parse($value)) {
                foreach ($matches as $search) {
                    $k = str_replace(['#{', '}'], '', $search);

                    $value = Str::replaceFirst($search, (string) data_get($arguments, $k), $value);
                }
            }
        } else {
            $value = implode(':', $arguments);
        }

        return $prefix . ':' . $value;
    }

    /**
     * Parse expression of value.
     */
    public static function parse(string $value): array
    {
        preg_match_all('/#\{[\w.]+}/', $value, $matches);

        return $matches[0];
    }
}

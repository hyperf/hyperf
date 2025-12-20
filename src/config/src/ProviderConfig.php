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

namespace Hyperf\Config;

use Hyperf\Di\Definition\PriorityDefinition;
use Hyperf\Support\Composer;

use function class_exists;
use function is_string;
use function method_exists;

/**
 * Provider config allow the components set the configs to application.
 */
class ProviderConfig
{
    private static array $providerConfigs = [];

    /**
     * Load and merge all provider configs from components.
     * Notice that this method will cached the config result into a static property,
     * call ProviderConfig::clear() method if you want to reset the static property.
     */
    public static function load(): array
    {
        if (! static::$providerConfigs) {
            $providers = Composer::getMergedExtra('hyperf')['config'] ?? [];
            static::$providerConfigs = static::loadProviders($providers);
        }
        return static::$providerConfigs;
    }

    public static function clear(): void
    {
        static::$providerConfigs = [];
    }

    protected static function loadProviders(array $providers): array
    {
        $providerConfigs = [];
        foreach ($providers as $provider) {
            if (is_string($provider) && class_exists($provider) && method_exists($provider, '__invoke')) {
                $providerConfigs[] = (new $provider())();
            }
        }

        return static::merge(...$providerConfigs);
    }

    protected static function merge(...$arrays): array
    {
        if (empty($arrays)) {
            return [];
        }

        $result = array_reduce(
            array_slice($arrays, 1),
            [static::class, 'mergeTwo'],
            $arrays[0]
        );

        if (isset($result['dependencies'])) {
            $result['dependencies'] = [];
            foreach ($arrays as $item) {
                foreach ($item['dependencies'] ?? [] as $key => $value) {
                    $depend = $result['dependencies'][$key] ?? null;
                    if (! $depend instanceof PriorityDefinition) {
                        $result['dependencies'][$key] = $value;
                        continue;
                    }

                    if ($value instanceof PriorityDefinition) {
                        $depend->merge($value);
                    }
                }
            }
        }

        return $result;
    }

    private static function mergeTwo(array $base, array $override): array
    {
        $result = $base;

        foreach ($override as $key => $value) {
            if (is_int($key)) {
                if (! in_array($value, $result, true)) {
                    $result[] = $value;
                }
            } elseif (! array_key_exists($key, $result)) {
                $result[$key] = $value;
            } elseif (is_array($value) && is_array($result[$key])) {
                $result[$key] = static::mergeTwo($result[$key], $value);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}

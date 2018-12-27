<?php

namespace Hyperf\Config;

use Hyperf\Utils\Arr;
use Hyperf\Utils\Composer;
use function is_string;
use function class_exists;
use function method_exists;
use function array_replace_recursive;

/**
 * Provider config allow the components set the configs to application.
 */
class ProviderConfig
{

    /**
     * @var array
     */
    private static $privoderConfigs = [];

    /**
     * Load and merge all provider configs from components.
     * Notice that this method will cached the config result into a static property,
     * call ProviderConfig::clear() method if you want to reset the static property.
     */
    public static function load(): array
    {
        if (! static::$privoderConfigs) {
            $config = [];
            $providers = Composer::getMergedExtra('hyperf')['config'];
            foreach ($providers ?? [] as $provider) {
                if (is_string($provider) && class_exists($provider) && method_exists($provider, '__invoke')) {
                    $providerConfig = (new $provider)();
                    $config = array_merge_recursive($config, $providerConfig);
                }
            }

            static::$privoderConfigs = Arr::unique($config);
            unset($config, $providerConfig);
        }
        return static::$privoderConfigs;
    }

    public function clear(): void
    {
        static::$privoderConfigs = [];
    }

}
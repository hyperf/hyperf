<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Di\Annotation;

use Hyperf\Config\ProviderConfig;

final class ScanConfig
{
    /**
     * The paths should be scaned everytime.
     *
     * @var array
     */
    private $paths;

    /**
     * The namespaces should be cached after scaned.
     *
     * @var array
     */
    private $cache;

    /**
     * @var array
     */
    private $collectors;

    /**
     * @var array
     */
    private $ignoreAnnotations;

    /**
     * @var array
     */
    private $globalImports;

    /**
     * @var array
     */
    private $dependencies;

    /**
     * @var ScanConfig
     */
    private static $instance;

    public function __construct(
        array $paths = [],
        array $dependencies = [],
        array $ignoreAnnotations = [],
        array $globalImports = [],
        array $cacheNamespaces = [],
        array $collectors
    ) {
        $this->paths = $paths;
        $this->dependencies = $dependencies;
        $this->ignoreAnnotations = $ignoreAnnotations;
        $this->globalImports = $globalImports;
        $this->cacheNamespaces = $cacheNamespaces;
        $this->collectors = $collectors;
    }

    public function getPaths(): array
    {
        return $this->paths;
    }

    public function getCacheNamespaces(): array
    {
        return $this->cacheNamespaces;
    }

    public function getCollectors(): array
    {
        return $this->collectors;
    }

    public function getIgnoreAnnotations(): array
    {
        return $this->ignoreAnnotations;
    }

    public function getGlobalImports(): array
    {
        return $this->globalImports;
    }

    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    public static function instance(): self
    {
        if (self::$instance) {
            return self::$instance;
        }

        [$config, $serverDependencies] = static::initConfigByFile(BASE_PATH . '/config');

        return self::$instance = new self(
            $config['paths'] ?? [],
            $serverDependencies ?? [],
            $config['ignore_annotations'] ?? [],
            $config['global_imports'] ?? [],
            $config['cache_namespaces'] ?? [],
            $config['collectors'] ?? []
        );
    }

    private static function initConfigByFile(string $configDir): array
    {
        $config = [];
        $configFromProviders = [];
        if (class_exists(ProviderConfig::class)) {
            $configFromProviders = ProviderConfig::load();
        }

        $serverDependencies = $configFromProviders['dependencies'] ?? [];
        if (file_exists($configDir . '/autoload/dependencies.php')) {
            $definitions = include $configDir . '/autoload/dependencies.php';
            $serverDependencies = array_replace($serverDependencies, $definitions ?? []);
        }

        $config = static::allocateConfigValue($configFromProviders['annotations'], $config);

        // Load the config/autoload/annotations.php and merge the config
        if (file_exists($configDir . '/autoload/annotations.php')) {
            $annotations = include $configDir . '/autoload/annotations.php';
            $config = static::allocateConfigValue($annotations, $config);
        }

        // Load the config/config.php and merge the config
        if (file_exists($configDir . '/config.php')) {
            $configContent = include $configDir . '/config.php';
            if (isset($configContent['annotations'])) {
                $config = static::allocateConfigValue($configContent['annotations'], $config);
            }
        }
        return [$config, $serverDependencies];
    }

    private static function allocateConfigValue(array $content, array $config): array
    {
        if (! isset($content['scan'])) {
            return [];
        }
        foreach ($content['scan'] as $key => $value) {
            if (! isset($config[$key])) {
                $config[$key] = [];
            }
            if (! is_array($value)) {
                $value = [$value];
            }
            $config[$key] = array_merge($config[$key], $value);
        }
        return $config;
    }
}

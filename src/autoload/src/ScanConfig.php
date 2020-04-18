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
namespace Hyperf\Autoload;

use Hyperf\Config\ProviderConfig;

final class ScanConfig
{
    /**
     * The paths should be scaned everytime.
     * @var array
     */
    private $paths;

    /**
     * The namespaces should be cached after scaned.
     * @var array
     */
    private $shouldCached;

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

    public function __construct(array $paths = [], array $dependencies = [], array $ignoreAnnotations = [], array $globalImports = [], array $shouldCached = [], array $collectors)
    {
        $this->paths = $paths;
        $this->dependencies = $dependencies;
        $this->ignoreAnnotations = $ignoreAnnotations;
        $this->globalImports = $globalImports;
        $this->shouldCached = $shouldCached;
        $this->collectors = $collectors;
    }

    public function getPaths(): array
    {
        return $this->paths;
    }

    public function getShouldCached(): array
    {
        return $this->shouldCached;
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

        $configDir = BASE_PATH . '/config';
        $configFromProviders = [];
        if (class_exists(ProviderConfig::class)) {
            $configFromProviders = ProviderConfig::load();
        }

        $serverDependencies = $configFromProviders['dependencies'] ?? [];
        if (file_exists($configDir . '/autoload/dependencies.php')) {
            $definitions = include $configDir . '/autoload/dependencies.php';
            $serverDependencies = array_replace($serverDependencies, $definitions ?? []);
        }

        $paths = $configFromProviders['annotations']['scan']['paths'] ?? [];
        $ignoreAnnotations = $configFromProviders['annotations']['scan']['ignore_annotations'] ?? [];
        $globalImports = $configFromProviders['annotations']['scan']['global_imports'] ?? [];
        $shouldCached = $configFromProviders['annotations']['scan']['should_cached_namespaces'] ?? [];
        $collectors = $configFromProviders['annotations']['scan']['collectors'] ?? [];

        // Load the config/autoload/annotations.php and merge the config
        if (file_exists($configDir . '/autoload/annotations.php')) {
            $annotations = include $configDir . '/autoload/annotations.php';
            $paths = array_merge($paths, $annotations['scan']['paths'] ?? []);
            $ignoreAnnotations = array_merge($ignoreAnnotations, $annotations['scan']['ignore_annotations'] ?? []);
            $globalImports = array_merge($globalImports, $annotations['scan']['global_imports'] ?? []);
            $shouldCached = array_merge($shouldCached, $annotations['scan']['should_cached_namespaces'] ?? []);
            $collectors = array_merge($collectors, $annotations['scan']['collectors'] ?? []);
        }

        // Load the config/config.php and merge the config
        if (file_exists($configDir . '/config.php')) {
            $configContent = include $configDir . '/config.php';
            if (isset($configContent['annotations'])) {
                $paths = array_merge($paths, $configContent['annotations']['scan']['paths'] ?? []);
                $ignoreAnnotations = array_merge($ignoreAnnotations, $configContent['annotations']['scan']['ignore_annotations'] ?? []);
                $globalImports = array_merge($globalImports, $configContent['annotations']['scan']['global_imports'] ?? []);
                $shouldCached = array_merge($shouldCached, $configContent['annotations']['scan']['should_cached_namespaces'] ?? []);
                $collectors = array_merge($collectors, $configContent['annotations']['scan']['collectors'] ?? []);
            }
        }

        return self::$instance = new self($paths, $serverDependencies, $ignoreAnnotations, $globalImports, $shouldCached, $collectors);
    }
}

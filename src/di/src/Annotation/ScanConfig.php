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

namespace Hyperf\Di\Annotation;

use Hyperf\Config\ProviderConfig;

use function Hyperf\Support\value;

class ScanConfig
{
    private static ?ScanConfig $instance = null;

    /**
     * @param array $paths the paths should be scanned everytime
     */
    public function __construct(
        private bool $cacheable,
        private string $configDir,
        private array $paths = [],
        private array $dependencies = [],
        private array $ignoreAnnotations = [],
        private array $globalImports = [],
        private array $collectors = [],
        private array $classMap = []
    ) {
    }

    public function isCacheable(): bool
    {
        return $this->cacheable;
    }

    public function getConfigDir(): string
    {
        return $this->configDir;
    }

    public function getPaths(): array
    {
        return $this->paths;
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

    public function getClassMap(): array
    {
        return $this->classMap;
    }

    public static function instance(string $configDir): self
    {
        if (self::$instance) {
            return self::$instance;
        }

        $configDir = rtrim($configDir, '/');

        [$config, $serverDependencies, $cacheable] = static::initConfigByFile($configDir);

        return self::$instance = new self(
            $cacheable,
            $configDir,
            $config['paths'] ?? [],
            $serverDependencies ?? [],
            $config['ignore_annotations'] ?? [],
            $config['global_imports'] ?? [],
            $config['collectors'] ?? [],
            $config['class_map'] ?? []
        );
    }

    private static function initConfigByFile(string $configDir): array
    {
        $config = [];
        $configFromProviders = [];
        $cacheable = false;
        if (class_exists(ProviderConfig::class)) {
            $configFromProviders = ProviderConfig::load();
        }

        $serverDependencies = $configFromProviders['dependencies'] ?? [];
        if (file_exists($configDir . '/autoload/dependencies.php')) {
            $definitions = include $configDir . '/autoload/dependencies.php';
            $serverDependencies = array_replace($serverDependencies, $definitions ?? []);
        }

        $config = static::allocateConfigValue($configFromProviders['annotations'] ?? [], $config);

        // Load the config/autoload/annotations.php and merge the config
        if (file_exists($configDir . '/autoload/annotations.php')) {
            $annotations = include $configDir . '/autoload/annotations.php';
            $config = static::allocateConfigValue($annotations, $config);
        }

        // Load the config/config.php and merge the config
        if (file_exists($configDir . '/config.php')) {
            $configContent = include $configDir . '/config.php';
            $appEnv = $configContent['app_env'] ?? 'dev';
            $cacheable = value($configContent['scan_cacheable'] ?? $appEnv === 'prod');
            if (isset($configContent['annotations'])) {
                $config = static::allocateConfigValue($configContent['annotations'], $config);
            }
        }

        return [$config, $serverDependencies, $cacheable];
    }

    private static function allocateConfigValue(array $content, array $config): array
    {
        if (! isset($content['scan'])) {
            return $config;
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

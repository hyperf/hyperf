<?php


namespace Hyperf\Autoload;


use Hyperf\Config\ProviderConfig;

final class ScanConfig
{
    /**
     * @var array
     */
    protected $paths;

    /**
     * @var array
     */
    protected $ignoreAnnotations;

    /**
     * @var array
     */
    protected $dependencies;

    /**
     * @var ScanConfig
     */
    protected static $instance;

    public function __construct(array $paths = [], array $ignoreAnnotations = [], array $dependencies = [])
    {
        $this->paths = $paths;
        $this->ignoreAnnotations = $ignoreAnnotations;
        $this->dependencies = $dependencies;
    }

    public function getPaths(): array
    {
        return $this->paths;
    }

    public function getIgnoreAnnotations(): array
    {
        return $this->ignoreAnnotations;
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

        $scanDirs = $configFromProviders['annotations']['scan']['paths'] ?? [];
        $ignoreAnnotations = $configFromProviders['annotations']['scan']['ignore_annotations'] ?? [];

        // Load the config/autoload/annotations.php and merge the config
        if (file_exists($configDir . '/autoload/annotations.php')) {
            $annotations = include $configDir . '/autoload/annotations.php';
            $scanDirs = array_merge($scanDirs, $annotations['scan']['paths'] ?? []);
            $ignoreAnnotations = array_merge($ignoreAnnotations, $annotations['scan']['ignore_annotations'] ?? []);
        }

        // Load the config/config.php and merge the config
        if (file_exists($configDir . '/config.php')) {
            $configContent = include $configDir . '/config.php';
            if (isset($configContent['annotations'])) {
                $scanDirs = array_merge($scanDirs, $configContent['annotations']['scan']['paths'] ?? []);
                $ignoreAnnotations = array_merge($ignoreAnnotations, $configContent['annotations']['scan']['ignore_annotations'] ?? []);
            }
        }

        return self::$instance = new self($scanDirs, $ignoreAnnotations,$serverDependencies);
    }
}

<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Di\Definition;

use Hyperf\Config\ProviderConfig;
use Hyperf\Di\Exception\Exception;

class DefinitionSourceFactory
{
    /**
     * @var bool
     */
    protected $enableCache = false;

    /**
     * @var string
     */
    protected $baseUri;

    public function __construct(bool $enableCache = false)
    {
        $this->enableCache = $enableCache;

        if (! defined('BASE_PATH')) {
            throw new Exception('BASE_PATH is not defined.');
        }

        $this->baseUri = BASE_PATH;
    }

    public function __invoke()
    {
        $configDir = $this->baseUri . '/config';

        $configFromProviders = [];
        if (class_exists(ProviderConfig::class)) {
            $configFromProviders = ProviderConfig::load();
        }

        $serverDependencies = $configFromProviders['dependencies'] ?? [];
        if (file_exists($configDir . '/dependencies.php')) {
            $definitions = include $configDir . '/dependencies.php';
            $serverDependencies = array_replace($serverDependencies, $definitions['dependencies'] ?? []);
        }

        $scanDirs = $configFromProviders['scan']['paths'] ?? [];
        $ignoreAnnotations = [];
        $collectors = $configFromProviders['scan']['collectors'] ?? [];

        if (file_exists($configDir . '/autoload/annotations.php')) {
            $annotations = include $configDir . '/autoload/annotations.php';
            $scanDirs = array_merge($scanDirs, $annotations['scan']['paths'] ?? []);
            $ignoreAnnotations = $annotations['scan']['ignore_annotations'] ?? [];
            $collectors = array_merge($collectors, $annotations['scan']['collectors'] ?? []);
        }

        $scanConfig = new ScanConfig($scanDirs, $ignoreAnnotations, $collectors);

        return new DefinitionSource($serverDependencies, $scanConfig, $this->enableCache);
    }
}

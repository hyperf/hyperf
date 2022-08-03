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
namespace Hyperf\Di\Definition;

use Hyperf\Config\ProviderConfig;
use Hyperf\Di\Exception\Exception;

class DefinitionSourceFactory
{
    public function __invoke(): DefinitionSource
    {
        if (! defined('BASE_PATH')) {
            throw new Exception('BASE_PATH is not defined.');
        }

        $serverDependencies = $configFromProviders = [];
        if (class_exists(ProviderConfig::class)) {
            $configFromProviders = ProviderConfig::load();
        }

        $configDir = BASE_PATH . '/config/autoload/dependencies.php';

        if (file_exists($configDir)) {
            $definitions = include $configDir;
            $serverDependencies = array_replace($configFromProviders['dependencies'] ?? [], $definitions ?? []);
        }

        return new DefinitionSource($serverDependencies);
    }
}

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
    protected string $baseUri;

    public function __construct(protected bool $enableCache = false)
    {
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
        if (file_exists($configDir . '/autoload/dependencies.php')) {
            $definitions = include $configDir . '/autoload/dependencies.php';
            $serverDependencies = array_replace($serverDependencies, $definitions ?? []);
        }

        return new DefinitionSource($serverDependencies);
    }
}

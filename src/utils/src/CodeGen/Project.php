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
namespace Hyperf\Utils\CodeGen;

use Hyperf\Utils\Composer;
use Hyperf\Utils\Str;

/**
 * Read composer.json autoload psr-4 rules to figure out the namespace or path.
 */
class Project
{
    public function namespace(string $path): string
    {
        $ext = pathinfo($path, PATHINFO_EXTENSION);

        if ($ext !== '') {
            $path = substr($path, 0, -(strlen($ext) + 1));
        } else {
            $path = trim($path, '/') . '/';
        }

        foreach ($this->getAutoloadRules() as $prefix => $prefixPath) {
            if ($this->isRootNamespace($prefix) || strpos($path, $prefixPath) === 0) {
                return $prefix . str_replace('/', '\\', substr($path, strlen($prefixPath)));
            }
        }
        throw new \RuntimeException("Invalid project path: {$path}");
    }

    public function className(string $path): string
    {
        return $this->namespace($path);
    }

    public function path(string $name, $extension = '.php'): string
    {
        if (Str::endsWith($name, '\\')) {
            $extension = '';
        }

        foreach ($this->getAutoloadRules() as $prefix => $prefixPath) {
            if ($this->isRootNamespace($prefix) || strpos($name, $prefix) === 0) {
                return $prefixPath . str_replace('\\', '/', substr($name, strlen($prefix))) . $extension;
            }
        }

        throw new \RuntimeException("Invalid class name: {$name}");
    }

    protected function isRootNamespace(string $namespace): bool
    {
        return $namespace === '';
    }

    protected function getAutoloadRules(): array
    {
        return data_get(Composer::getJsonContent(), 'autoload.psr-4', []);
    }
}

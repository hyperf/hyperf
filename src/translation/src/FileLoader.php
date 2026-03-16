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

namespace Hyperf\Translation;

use Hyperf\Contract\TranslatorLoaderInterface;
use Hyperf\Support\Filesystem\Filesystem;
use JsonException;
use RuntimeException;

use function Hyperf\Collection\collect;

class FileLoader implements TranslatorLoaderInterface
{
    /**
     * All of the registered paths to JSON translation files.
     */
    protected array $jsonPaths = [];

    /**
     * All of the namespace hints.
     */
    protected array $hints = [];

    /**
     * Create a new file loader instance.
     *
     * @param Filesystem $files the filesystem instance
     * @param string $path the default path for the loader
     */
    public function __construct(protected Filesystem $files, protected string $path)
    {
    }

    /**
     * Load the messages for the given locale.
     */
    public function load(string $locale, string $group, ?string $namespace = null): array
    {
        if ($group === '*' && $namespace === '*') {
            return $this->loadJsonPaths($locale);
        }

        if (is_null($namespace) || $namespace === '*') {
            return $this->loadPath($this->path, $locale, $group);
        }

        return $this->loadNamespaced($locale, $group, $namespace);
    }

    /**
     * Add a new namespace to the loader.
     */
    public function addNamespace(string $namespace, string $hint)
    {
        $this->hints[$namespace] = $hint;
    }

    /**
     * Add a new JSON path to the loader.
     */
    public function addJsonPath(string $path)
    {
        $this->jsonPaths[] = $path;
    }

    /**
     * Get an array of all the registered namespaces.
     */
    public function namespaces(): array
    {
        return $this->hints;
    }

    /**
     * Load a namespaced translation group.
     */
    protected function loadNamespaced(string $locale, string $group, string $namespace): array
    {
        if (isset($this->hints[$namespace])) {
            $lines = $this->loadPath($this->hints[$namespace], $locale, $group);

            return $this->loadNamespaceOverrides($lines, $locale, $group, $namespace);
        }

        return [];
    }

    /**
     * Load a local namespaced translation group for overrides.
     */
    protected function loadNamespaceOverrides(array $lines, string $locale, string $group, string $namespace): array
    {
        $file = "{$this->path}/vendor/{$namespace}/{$locale}/{$group}.php";

        if ($this->files->exists($file)) {
            return array_replace_recursive($lines, $this->files->getRequire($file));
        }

        return $lines;
    }

    /**
     * Load a locale from a given path.
     */
    protected function loadPath(string $path, string $locale, string $group): array
    {
        if ($this->files->exists($full = "{$path}/{$locale}/{$group}.php")) {
            return $this->files->getRequire($full);
        }

        return [];
    }

    /**
     * Load a locale from the given JSON file path.
     *
     * @return array
     * @throws RuntimeException
     */
    protected function loadJsonPaths(string $locale): iterable
    {
        return collect(array_merge($this->jsonPaths, [$this->path]))
            ->reduce(function ($output, $path) use ($locale) {
                if ($this->files->exists($full = "{$path}/{$locale}.json")) {
                    try {
                        $decoded = json_decode($this->files->get($full), true, 512, JSON_THROW_ON_ERROR);
                    } catch (JsonException $e) {
                        throw new RuntimeException("Translation file [{$full}] contains an invalid JSON structure.");
                    }

                    if (is_array($decoded)) {
                        $output = array_merge($output, $decoded);
                    }
                }

                return $output;
            }, []);
    }
}

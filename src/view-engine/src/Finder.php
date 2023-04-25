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
namespace Hyperf\ViewEngine;

use Hyperf\Support\Filesystem\Filesystem;
use Hyperf\ViewEngine\Contract\FinderInterface;
use InvalidArgumentException;

class Finder implements FinderInterface
{
    /**
     * The array of active view paths.
     */
    protected array $paths;

    /**
     * The array of views that have been located.
     */
    protected array $views = [];

    /**
     * The namespace to file path hints.
     */
    protected array $hints = [];

    /**
     * Register a view extension with the finder.
     */
    protected array $extensions = ['blade.php', 'php', 'css', 'html'];

    /**
     * Create a new file view loader instance.
     */
    public function __construct(protected Filesystem $files, array $paths, array $extensions = null)
    {
        $this->paths = array_map([$this, 'resolvePath'], $paths);

        if (isset($extensions) && $extensions) {
            $this->extensions = $extensions;
        }
    }

    /**
     * Get the fully qualified location of the view.
     *
     * @return string
     */
    public function find(string $name)
    {
        if (isset($this->views[$name])) {
            return $this->views[$name];
        }

        if ($this->hasHintInformation($name = trim($name))) {
            return $this->views[$name] = $this->findNamespacedView($name);
        }

        return $this->views[$name] = $this->findInPaths($name, $this->paths);
    }

    /**
     * Add a location to the finder.
     */
    public function addLocation(string $location)
    {
        $this->paths[] = $this->resolvePath($location);
    }

    /**
     * Prepend a location to the finder.
     *
     * @param string $location
     */
    public function prependLocation($location)
    {
        array_unshift($this->paths, $this->resolvePath($location));
    }

    /**
     * Add a namespace hint to the finder.
     *
     * @param array|string $hints
     */
    public function addNamespace(string $namespace, $hints)
    {
        $hints = (array) $hints;

        if (isset($this->hints[$namespace])) {
            $hints = array_merge($this->hints[$namespace], $hints);
        }

        $this->hints[$namespace] = $hints;
    }

    /**
     * Prepend a namespace hint to the finder.
     *
     * @param array|string $hints
     */
    public function prependNamespace(string $namespace, $hints)
    {
        $hints = (array) $hints;

        if (isset($this->hints[$namespace])) {
            $hints = array_merge($hints, $this->hints[$namespace]);
        }

        $this->hints[$namespace] = $hints;
    }

    /**
     * Replace the namespace hints for the given namespace.
     *
     * @param array|string $hints
     */
    public function replaceNamespace(string $namespace, $hints)
    {
        $this->hints[$namespace] = (array) $hints;
    }

    /**
     * Register an extension with the view finder.
     */
    public function addExtension(string $extension)
    {
        if (($index = array_search($extension, $this->extensions)) !== false) {
            unset($this->extensions[$index]);
        }

        array_unshift($this->extensions, $extension);
    }

    /**
     * Returns whether or not the view name has any hint information.
     *
     * @param string $name
     * @return bool
     */
    public function hasHintInformation($name)
    {
        return strpos($name, static::HINT_PATH_DELIMITER) > 0;
    }

    /**
     * Flush the cache of located views.
     */
    public function flush()
    {
        $this->views = [];
    }

    /**
     * Get the filesystem instance.
     *
     * @return Filesystem
     */
    public function getFilesystem()
    {
        return $this->files;
    }

    /**
     * Set the active view paths.
     *
     * @param array $paths
     * @return $this
     */
    public function setPaths($paths)
    {
        $this->paths = $paths;

        return $this;
    }

    /**
     * Get the active view paths.
     *
     * @return array
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * Get the views that have been located.
     *
     * @return array
     */
    public function getViews()
    {
        return $this->views;
    }

    /**
     * Get the namespace to file path hints.
     *
     * @return array
     */
    public function getHints()
    {
        return $this->hints;
    }

    /**
     * Get registered extensions.
     *
     * @return array
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Get the path to a template with a named path.
     *
     * @param string $name
     * @return string
     */
    protected function findNamespacedView($name)
    {
        [$namespace, $view] = $this->parseNamespaceSegments($name);

        return $this->findInPaths($view, $this->hints[$namespace]);
    }

    /**
     * Get the segments of a template with a named path.
     *
     * @param string $name
     * @return array
     * @throws InvalidArgumentException
     */
    protected function parseNamespaceSegments($name)
    {
        $segments = explode(static::HINT_PATH_DELIMITER, $name);

        if (count($segments) !== 2) {
            throw new InvalidArgumentException("View [{$name}] has an invalid name.");
        }

        if (! isset($this->hints[$segments[0]])) {
            throw new InvalidArgumentException("No hint path defined for [{$segments[0]}].");
        }

        return $segments;
    }

    /**
     * Find the given view in the list of paths.
     *
     * @param string $name
     * @param array $paths
     * @return string
     * @throws InvalidArgumentException
     */
    protected function findInPaths($name, $paths)
    {
        foreach ((array) $paths as $path) {
            foreach ($this->getPossibleViewFiles($name) as $file) {
                if ($this->files->exists($viewPath = $path . '/' . $file)) {
                    return $viewPath;
                }
            }
        }

        throw new InvalidArgumentException("View [{$name}] not found.");
    }

    /**
     * Get an array of possible view files.
     *
     * @param string $name
     * @return array
     */
    protected function getPossibleViewFiles($name)
    {
        return array_map(fn ($extension) => str_replace('.', '/', $name) . '.' . $extension, $this->extensions);
    }

    /**
     * Resolve the path.
     *
     * @param string $path
     * @return string
     */
    protected function resolvePath($path)
    {
        return realpath($path) ?: $path;
    }
}

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
namespace Hyperf\ViewEngine\Contract;

use Closure;
use Hyperf\Utils\Contracts\Arrayable;

interface FactoryInterface
{
    /**
     * Determine if a given view exists.
     */
    public function exists(string $view): bool;

    /**
     * Get the evaluated view contents for the given path.
     *
     * @param array|Arrayable $data
     */
    public function file(string $path, $data = [], array $mergeData = []): ViewInterface;

    /**
     * Get the evaluated view contents for the given view.
     *
     * @param array|Arrayable $data
     */
    public function make(string $view, $data = [], array $mergeData = []): ViewInterface;

    /**
     * Add a piece of shared data to the environment.
     *
     * @param array|string $key
     * @param mixed $value
     * @return mixed
     */
    public function share($key, $value = null);

    /**
     * Register a view composer event.
     *
     * @param array|string $views
     * @param Closure|string $callback
     */
    public function composer($views, $callback);

    /**
     * Register a view creator event.
     *
     * @param array|string $views
     * @param Closure|string $callback
     */
    public function creator($views, $callback);

    /**
     * Add a new namespace to the loader.
     *
     * @param array|string $hints
     * @return $this
     */
    public function addNamespace(string $namespace, $hints);

    /**
     * Replace the namespace hints for the given namespace.
     *
     * @param array|string $hints
     * @return $this
     */
    public function replaceNamespace(string $namespace, $hints);
}

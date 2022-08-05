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
use Hyperf\Contract\Arrayable;

interface FactoryInterface
{
    /**
     * Determine if a given view exists.
     */
    public function exists(string $view): bool;

    /**
     * Get the evaluated view contents for the given path.
     */
    public function file(string $path, array|\Hyperf\Contract\Arrayable $data = [], array $mergeData = []): ViewInterface;

    /**
     * Get the evaluated view contents for the given view.
     */
    public function make(string $view, array|\Hyperf\Contract\Arrayable $data = [], array $mergeData = []): ViewInterface;

    /**
     * Add a piece of shared data to the environment.
     *
     * @param mixed $value
     * @return mixed
     */
    public function share(array|string $key, $value = null);

    /**
     * Register a view composer event.
     */
    public function composer(array|string $views, Closure|string $callback): array;

    /**
     * Register a view creator event.
     */
    public function creator(array|string $views, Closure|string $callback): array;

    /**
     * Add a new namespace to the loader.
     *
     * @return $this
     */
    public function addNamespace(string $namespace, array|string $hints);

    /**
     * Replace the namespace hints for the given namespace.
     *
     * @return $this
     */
    public function replaceNamespace(string $namespace, array|string $hints);
}

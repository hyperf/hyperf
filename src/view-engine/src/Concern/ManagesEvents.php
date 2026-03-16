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

namespace Hyperf\ViewEngine\Concern;

use Closure;
use Hyperf\Stringable\Str;
use Hyperf\ViewEngine\Blade;
use Hyperf\ViewEngine\Contract\ViewInterface;

trait ManagesEvents
{
    /**
     * Register a view creator event.
     */
    public function creator(array|string $views, Closure|string $callback): array
    {
        $creators = [];

        foreach ((array) $views as $view) {
            $creators[] = $this->addViewEvent($view, $callback, 'creating: ');
        }

        return $creators;
    }

    /**
     * Register multiple view composers via an array.
     */
    public function composers(array $composers): array
    {
        $registered = [];

        foreach ($composers as $callback => $views) {
            $registered = array_merge($registered, $this->composer($views, $callback));
        }

        return $registered;
    }

    /**
     * Register a view composer event.
     */
    public function composer(array|string $views, Closure|string $callback): array
    {
        $composers = [];

        foreach ((array) $views as $view) {
            $composers[] = $this->addViewEvent($view, $callback, 'composing: ');
        }

        return $composers;
    }

    /**
     * Call the composer for a given view.
     */
    public function callComposer(ViewInterface $view)
    {
        // @TODO
        // $this->events->dispatch('composing: '.$view->name(), [$view]);
    }

    /**
     * Call the creator for a given view.
     */
    public function callCreator(ViewInterface $view)
    {
        // @TODO
        // $this->events->dispatch('creating: '.$view->name(), [$view]);
    }

    /**
     * Add an event for a given view.
     *
     * @param string $view
     * @param string $prefix
     * @return null|Closure
     */
    protected function addViewEvent($view, Closure|string $callback, $prefix = 'composing: ')
    {
        $view = $this->normalizeName($view);

        if ($callback instanceof Closure) {
            $this->addEventListener($prefix . $view, $callback);

            return $callback;
        }

        return $this->addClassEvent($view, $callback, $prefix);
    }

    /**
     * Register a class based view composer.
     *
     * @param string $view
     * @param string $class
     * @param string $prefix
     * @return Closure
     */
    protected function addClassEvent($view, $class, $prefix)
    {
        $name = $prefix . $view;

        // When registering a class based view "composer", we will simply resolve the
        // classes from the application IoC container then call the compose method
        // on the instance. This allows for convenient, testable view composers.
        $callback = $this->buildClassEventCallback(
            $class,
            $prefix
        );

        $this->addEventListener($name, $callback);

        return $callback;
    }

    /**
     * Build a class based container callback Closure.
     *
     * @param string $class
     * @param string $prefix
     * @return Closure
     */
    protected function buildClassEventCallback($class, $prefix)
    {
        [$class, $method] = $this->parseClassEvent($class, $prefix);

        // Once we have the class and method name, we can build the Closure to resolve
        // the instance out of the IoC container and call the method on it with the
        // given arguments that are passed to the Closure as the composer's data.
        return fn () => call_user_func_array(
            [Blade::container()->make($class), $method],
            func_get_args()
        );
    }

    /**
     * Parse a class based composer name.
     *
     * @param string $class
     * @param string $prefix
     * @return array
     */
    protected function parseClassEvent($class, $prefix)
    {
        return Str::parseCallback($class, $this->classEventMethodForPrefix($prefix));
    }

    /**
     * Determine the class event method based on the given prefix.
     *
     * @param string $prefix
     * @return string
     */
    protected function classEventMethodForPrefix($prefix)
    {
        return Str::contains($prefix, 'composing') ? 'compose' : 'create';
    }

    /**
     * Add a listener to the event dispatcher.
     *
     * @param string $name
     * @param Closure $callback
     */
    protected function addEventListener($name, $callback)
    {
        if (Str::contains($name, '*')) {
            $callback = fn ($name, array $data) => $callback($data[0]);
        }

        // @TODO
        // $this->events->listen($name, $callback);
    }
}

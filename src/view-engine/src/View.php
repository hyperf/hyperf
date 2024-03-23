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

use ArrayAccess;
use BadMethodCallException;
use Hyperf\Contract\Arrayable;
use Hyperf\Contract\MessageBag;
use Hyperf\Contract\MessageProvider;
use Hyperf\Macroable\Macroable;
use Hyperf\Stringable\Str;
use Hyperf\ViewEngine\Contract\EngineInterface;
use Hyperf\ViewEngine\Contract\Htmlable;
use Hyperf\ViewEngine\Contract\Renderable;
use Hyperf\ViewEngine\Contract\ViewInterface;
use Throwable;

class View implements ArrayAccess, Htmlable, ViewInterface
{
    use Macroable {
        __call as macroCall;
    }

    /**
     * The array of view data.
     *
     * @var array
     */
    protected $data;

    /**
     * Create a new view instance.
     *
     * @param mixed $data
     */
    /**
     * Create a new view instance.
     *
     * @param Factory $factory the view factory instance
     * @param EngineInterface $engine the engine implementation
     * @param string $view the name of the view
     * @param string $path the path to the view file
     * @param array|Arrayable $data
     */
    public function __construct(
        protected Factory $factory,
        protected EngineInterface $engine,
        protected string $view,
        protected string $path,
        $data = []
    ) {
        $this->data = $data instanceof Arrayable ? $data->toArray() : (array) $data;
    }

    /**
     * Set a piece of data on the view.
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        $this->with($key, $value);
    }

    /**
     * Check if a piece of data is bound to the view.
     *
     * @param string $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Remove a piece of bound data from the view.
     *
     * @param string $key
     */
    public function __unset($key)
    {
        unset($this->data[$key]);
    }

    /**
     * Dynamically bind parameters to the view.
     *
     * @param string $method
     * @param array $parameters
     * @return View
     * @throws BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        if (! Str::startsWith($method, 'with')) {
            throw new BadMethodCallException(sprintf(
                'Method %s::%s does not exist.',
                static::class,
                $method
            ));
        }

        return $this->with(Str::camel(substr($method, 4)), $parameters[0]);
    }

    /**
     * Get the string contents of the view.
     *
     * @throws Throwable
     */
    public function __toString(): string
    {
        return (string) $this->render();
    }

    /**
     * Get the string contents of the view.
     *
     * @throws Throwable
     */
    public function render(?callable $callback = null): array|string
    {
        try {
            $contents = $this->renderContents();

            $response = isset($callback) ? $callback($this, $contents) : null;

            // Once we have the contents of the view, we will flush the sections if we are
            // done rendering all views so that there is nothing left hanging over when
            // another view gets rendered in the future by the application developer.
            $this->factory->flushStateIfDoneRendering();

            return ! is_null($response) ? $response : $contents;
        } catch (Throwable $e) {
            $this->factory->flushState();

            throw $e;
        }
    }

    /**
     * Get the data bound to the view instance.
     *
     * @return array
     */
    public function gatherData()
    {
        $data = array_merge($this->factory->getShared(), $this->data);

        foreach ($data as $key => $value) {
            if ($value instanceof Renderable) {
                $data[$key] = $value->render();
            }
        }

        return $data;
    }

    /**
     * Get the sections of the rendered view.
     *
     * @return array
     * @throws Throwable
     */
    public function renderSections()
    {
        return $this->render(fn () => $this->factory->getSections());
    }

    /**
     * Add a piece of data to the view.
     *
     * @param array|string $key
     * @param mixed $value
     * @return $this
     */
    public function with($key, $value = null)
    {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }

        return $this;
    }

    /**
     * Add a view instance to the view data.
     *
     * @param string $key
     * @param string $view
     * @return $this
     */
    public function nest($key, $view, array $data = [])
    {
        return $this->with($key, $this->factory->make($view, $data));
    }

    /**
     * Add validation errors to the view.
     *
     * @param string $bag
     * @return $this
     */
    public function withErrors(array|MessageProvider $provider, $bag = 'default')
    {
        return $this->with('errors', (new ViewErrorBag())->put(
            $bag,
            $this->formatErrors($provider)
        ));
    }

    /**
     * Get the name of the view.
     */
    public function name(): string
    {
        return $this->getName();
    }

    /**
     * Get the name of the view.
     */
    public function getName(): string
    {
        return $this->view;
    }

    /**
     * Get the array of view data.
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Get the path to the view file.
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Set the path to the view.
     */
    public function setPath(string $path)
    {
        $this->path = $path;
    }

    /**
     * Get the view factory instance.
     *
     * @return Factory
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * Get the view's rendering engine.
     *
     * @return EngineInterface
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * Determine if a piece of data is bound.
     */
    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->data);
    }

    /**
     * Get a piece of bound data to the view.
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->data[$offset];
    }

    /**
     * Set a piece of data on the view.
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->with($offset, $value);
    }

    /**
     * Unset a piece of data from the view.
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->data[$offset]);
    }

    /**
     * Get a piece of data from the view.
     *
     * @param string $key
     * @return mixed
     */
    public function &__get($key)
    {
        return $this->data[$key];
    }

    /**
     * Get content as a string of HTML.
     *
     * @return string
     */
    public function toHtml()
    {
        return $this->render();
    }

    /**
     * Get the contents of the view instance.
     *
     * @return string
     */
    protected function renderContents()
    {
        // We will keep track of the amount of views being rendered so we can flush
        // the section after the complete rendering operation is done. This will
        // clear out the sections for any separate views that may be rendered.
        $this->factory->incrementRender();

        $this->factory->callComposer($this);

        $contents = $this->getContents();

        // Once we've finished rendering the view, we'll decrement the render count
        // so that each sections get flushed out next time a view is created and
        // no old sections are staying around in the memory of an environment.
        $this->factory->decrementRender();

        return $contents;
    }

    /**
     * Get the evaluated contents of the view.
     *
     * @return string
     */
    protected function getContents()
    {
        return $this->engine->get($this->path, $this->gatherData());
    }

    /**
     * Parse the given errors into an appropriate value.
     */
    protected function formatErrors(array|MessageProvider|string $provider): \Hyperf\Support\MessageBag|MessageBag
    {
        return $provider instanceof MessageProvider
            ? $provider->getMessageBag()
            : new \Hyperf\Support\MessageBag((array) $provider);
    }
}

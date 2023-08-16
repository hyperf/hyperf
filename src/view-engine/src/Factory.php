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

use Closure;
use Hyperf\Collection\Arr;
use Hyperf\Contract\Arrayable;
use Hyperf\Macroable\Macroable;
use Hyperf\Stringable\Str;
use Hyperf\ViewEngine\Contract\EngineInterface;
use Hyperf\ViewEngine\Contract\EngineResolverInterface;
use Hyperf\ViewEngine\Contract\FactoryInterface;
use Hyperf\ViewEngine\Contract\FinderInterface;
use Hyperf\ViewEngine\Contract\ViewInterface;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

use function Hyperf\Tappable\tap;

class Factory implements FactoryInterface
{
    use Macroable;
    use Concern\ManagesComponents;
    use Concern\ManagesEvents;
    use Concern\ManagesLayouts;
    use Concern\ManagesLoops;
    use Concern\ManagesStacks;
    use Concern\ManagesTranslations;

    /**
     * The IoC container instance.
     */
    protected ?ContainerInterface $container = null;

    /**
     * Data that should be available to all templates.
     */
    protected array $shared = [];

    /**
     * The extension to engine bindings.
     */
    protected array $extensions = [
        'blade.php' => 'blade',
        'php' => 'php',
        'css' => 'file',
        'html' => 'file',
    ];

    /**
     * The view composer events.
     */
    protected array $composers = [];

    /**
     * The number of active rendering operations.
     */
    protected int $renderCount = 0;

    /**
     * The "once" block IDs that have been rendered.
     */
    protected array $renderedOnce = [];

    /**
     * Factory constructor.
     *
     * @param EngineResolverInterface $engines the engine implementation
     * @param FinderInterface $finder the view finder implementation
     * @param EventDispatcherInterface $events the event dispatcher instance
     */
    public function __construct(
        protected EngineResolverInterface $engines,
        protected FinderInterface $finder,
        protected EventDispatcherInterface $events
    ) {
        $this->share('__env', $this);
        $this->share('errors', new ViewErrorBag());
    }

    /**
     * Get the evaluated view contents for the given view.
     *
     * @param array|Arrayable $data
     */
    public function file(string $path, $data = [], array $mergeData = []): ViewInterface
    {
        $data = array_merge($mergeData, $this->parseData($data));

        return tap($this->viewInstance($path, $path, $data), function ($view) {
            $this->callCreator($view);
        });
    }

    /**
     * Get the evaluated view contents for the given view.
     *
     * @param array|Arrayable $data
     */
    public function make(string $view, $data = [], array $mergeData = []): ViewInterface
    {
        $path = $this->finder->find(
            $view = $this->normalizeName($view)
        );

        // Next, we will create the view instance and call the view creator for the view
        // which can set any data, etc. Then we will return the view instance back to
        // the caller for rendering or performing other view manipulations on this.
        $data = array_merge($mergeData, $this->parseData($data));

        return tap($this->viewInstance($view, $path, $data), function ($view) {
            $this->callCreator($view);
        });
    }

    /**
     * Get the first view that actually exists from the given list.
     *
     * @throws InvalidArgumentException
     */
    public function first(array $views, array|Arrayable $data = [], array $mergeData = []): ViewInterface
    {
        $view = Arr::first($views, fn ($view) => $this->exists($view));

        if (! $view) {
            throw new InvalidArgumentException('None of the views in the given array exist.');
        }

        return $this->make($view, $data, $mergeData);
    }

    /**
     * Get the rendered content of the view based on a given condition.
     *
     * @return string
     */
    public function renderWhen(bool $condition, string $view, array|Arrayable $data = [], array $mergeData = [])
    {
        if (! $condition) {
            return '';
        }

        return $this->make($view, $this->parseData($data), $mergeData)->render();
    }

    /**
     * Get the rendered content of the view based on the negation of a given condition.
     */
    public function renderUnless(bool $condition, string $view, array|Arrayable $data = [], array $mergeData = []): string
    {
        return $this->renderWhen(! $condition, $view, $data, $mergeData);
    }

    /**
     * Get the rendered contents of a partial from a loop.
     *
     * @return string
     */
    public function renderEach(string $view, array $data, string $iterator, string $empty = 'raw|')
    {
        $result = '';

        // If is actually data in the array, we will loop through the data and append
        // an instance of the partial view to the final result HTML passing in the
        // iterated value of this data array, allowing the views to access them.
        if (count($data) > 0) {
            foreach ($data as $key => $value) {
                $result .= $this->make(
                    $view,
                    ['key' => $key, $iterator => $value]
                )->render();
            }
        }

        // If there is no data in the array, we will render the contents of the empty
        // view. Alternatively, the "empty view" could be a raw string that begins
        // with "raw|" for convenience and to let this know that it is a string.
        else {
            $result = Str::startsWith($empty, 'raw|')
                ? substr($empty, 4)
                : $this->make($empty)->render();
        }

        return $result;
    }

    /**
     * Determine if a given view exists.
     */
    public function exists(string $view): bool
    {
        try {
            $this->finder->find($view);
        } catch (InvalidArgumentException) {
            return false;
        }

        return true;
    }

    /**
     * Get the appropriate view engine for the given path.
     *
     * @return EngineInterface
     * @throws InvalidArgumentException
     */
    public function getEngineFromPath(string $path)
    {
        if (! $extension = $this->getExtension($path)) {
            throw new InvalidArgumentException("Unrecognized extension in file: {$path}.");
        }

        $engine = $this->extensions[$extension];

        return $this->engines->resolve($engine);
    }

    /**
     * Add a piece of shared data to the environment.
     *
     * @param array|string $key
     * @param null|mixed $value
     * @return mixed
     */
    public function share($key, $value = null)
    {
        $keys = is_array($key) ? $key : [$key => $value];

        foreach ($keys as $key => $value) {
            $this->shared[$key] = $value;
        }

        return $value;
    }

    /**
     * Increment the rendering counter.
     */
    public function incrementRender()
    {
        ++$this->renderCount;
    }

    /**
     * Decrement the rendering counter.
     */
    public function decrementRender()
    {
        --$this->renderCount;
    }

    /**
     * Check if there are no active render operations.
     *
     * @return bool
     */
    public function doneRendering()
    {
        return $this->renderCount == 0;
    }

    /**
     * Determine if the given once token has been rendered.
     */
    public function hasRenderedOnce(string $id): bool
    {
        return isset($this->renderedOnce[$id]);
    }

    /**
     * Mark the given once token as having been rendered.
     */
    public function markAsRenderedOnce(string $id)
    {
        $this->renderedOnce[$id] = true;
    }

    /**
     * Add a location to the array of view locations.
     */
    public function addLocation(string $location)
    {
        $this->finder->addLocation($location);
    }

    /**
     * Add a new namespace to the loader.
     *
     * @param array|string $hints
     * @return $this
     */
    public function addNamespace(string $namespace, $hints)
    {
        $this->finder->addNamespace($namespace, $hints);

        return $this;
    }

    /**
     * Prepend a new namespace to the loader.
     *
     * @return $this
     */
    public function prependNamespace(string $namespace, array|string $hints)
    {
        $this->finder->prependNamespace($namespace, $hints);

        return $this;
    }

    /**
     * Replace the namespace hints for the given namespace.
     *
     * @param array|string $hints
     * @return $this
     */
    public function replaceNamespace(string $namespace, $hints)
    {
        $this->finder->replaceNamespace($namespace, $hints);

        return $this;
    }

    /**
     * Register a valid view extension and its engine.
     */
    public function addExtension(string $extension, string $engine, ?Closure $resolver = null)
    {
        $this->finder->addExtension($extension);

        if (isset($resolver)) {
            $this->engines->register($engine, $resolver);
        }

        unset($this->extensions[$extension]);

        $this->extensions = array_merge([$extension => $engine], $this->extensions);
    }

    /**
     * Flush all of the factory state like sections and stacks.
     */
    public function flushState()
    {
        $this->renderCount = 0;
        $this->renderedOnce = [];

        $this->flushSections();
        $this->flushStacks();
    }

    /**
     * Flush all of the section contents if done rendering.
     */
    public function flushStateIfDoneRendering()
    {
        if ($this->doneRendering()) {
            $this->flushState();
        }
    }

    /**
     * Get the extension to engine bindings.
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * Get the engine resolver instance.
     */
    public function getEngineResolver(): EngineResolverInterface
    {
        return $this->engines;
    }

    /**
     * Get the view finder instance.
     */
    public function getFinder(): FinderInterface
    {
        return $this->finder;
    }

    /**
     * Set the view finder instance.
     */
    public function setFinder(FinderInterface $finder)
    {
        $this->finder = $finder;
    }

    /**
     * Flush the cache of views located by the finder.
     */
    public function flushFinderCache()
    {
        $this->getFinder()->flush();
    }

    /**
     * Get the event dispatcher instance.
     */
    public function getDispatcher(): EventDispatcherInterface
    {
        return $this->events;
    }

    /**
     * Set the event dispatcher instance.
     */
    public function setDispatcher(EventDispatcherInterface $events)
    {
        $this->events = $events;
    }

    /**
     * Get the IoC container instance.
     */
    public function getContainer(): ContainerInterface
    {
        if (! $this->container) {
            $this->setContainer(Blade::container());
        }

        return $this->container;
    }

    /**
     * Set the IoC container instance.
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Get an item from the shared data.
     *
     * @param mixed $default
     * @return mixed
     */
    public function shared(string $key, $default = null)
    {
        return Arr::get($this->shared, $key, $default);
    }

    /**
     * Get all of the shared data for the environment.
     */
    public function getShared(): array
    {
        return $this->shared;
    }

    /**
     * Normalize a view name.
     */
    protected function normalizeName(string $name): string
    {
        $delimiter = FinderInterface::HINT_PATH_DELIMITER;

        if (! str_contains($name, $delimiter)) {
            return str_replace('/', '.', $name);
        }

        [$namespace, $name] = explode($delimiter, $name);

        return $namespace . $delimiter . str_replace('/', '.', $name);
    }

    /**
     * Parse the given data into a raw array.
     *
     * @return array
     */
    protected function parseData(array|Arrayable $data)
    {
        return $data instanceof Arrayable ? $data->toArray() : $data;
    }

    /**
     * Create a new view instance from the given arguments.
     *
     * @return ViewInterface
     */
    protected function viewInstance(string $view, string $path, array|Arrayable $data)
    {
        return new View($this, $this->getEngineFromPath($path), $view, $path, $data);
    }

    /**
     * Get the extension used by the view file.
     *
     * @return null|string
     */
    protected function getExtension(string $path)
    {
        $extensions = array_keys($this->extensions);

        return Arr::first($extensions, fn ($value) => Str::endsWith($path, '.' . $value));
    }
}

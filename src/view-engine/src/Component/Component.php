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

namespace Hyperf\ViewEngine\Component;

use Closure;
use Hyperf\Context\ApplicationContext;
use Hyperf\Stringable\Str;
use Hyperf\Support\Filesystem\Filesystem;
use Hyperf\ViewEngine\Blade;
use Hyperf\ViewEngine\Contract\FactoryInterface;
use Hyperf\ViewEngine\Contract\Htmlable;
use Hyperf\ViewEngine\View;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

use function Hyperf\Collection\collect;

abstract class Component
{
    /**
     * The component alias name.
     */
    public ?string $componentName = null;

    /**
     * The component attributes.
     */
    public ?ComponentAttributeBag $attributes = null;

    /**
     * The cache of public property names, keyed by class.
     *
     * @var array
     */
    protected static $propertyCache = [];

    /**
     * The cache of public method names, keyed by class.
     *
     * @var array
     */
    protected static $methodCache = [];

    /**
     * The properties / methods that should not be exposed to the component.
     *
     * @var array
     */
    protected $except = [];

    /**
     * Get the view / view contents that represent the component.
     *
     * @return Closure|Htmlable|string|View
     */
    abstract public function render(): mixed;

    /**
     * Resolve the Blade view or view file that should be used when rendering the component.
     *
     * @return Closure|Htmlable|string|View
     */
    public function resolveView(): mixed
    {
        $view = $this->render();

        if ($view instanceof View) {
            return $view;
        }

        if ($view instanceof Htmlable) {
            return $view;
        }

        $resolver = function ($view) {
            $factory = Blade::container()->get(FactoryInterface::class);

            return $factory->exists($view)
                ? $view
                : $this->createBladeViewFromString($view);
        };

        return $view instanceof Closure
            ? fn (array $data = []) => $resolver($view($data))
        : $resolver($view);
    }

    /**
     * Get the data that should be supplied to the view.
     */
    public function data(): array
    {
        $this->attributes = $this->attributes ?: new ComponentAttributeBag();

        return array_merge($this->extractPublicProperties(), $this->extractPublicMethods());
    }

    /**
     * Set the component alias name.
     *
     * @return $this
     */
    public function withName(string $name): static
    {
        $this->componentName = $name;

        return $this;
    }

    /**
     * Set the extra attributes that the component should make available.
     *
     * @return $this
     */
    public function withAttributes(array $attributes): static
    {
        $this->attributes = $this->attributes ?: new ComponentAttributeBag();

        $this->attributes->setAttributes($attributes);

        return $this;
    }

    /**
     * Determine if the component should be rendered.
     */
    public function shouldRender(): bool
    {
        return true;
    }

    /**
     * Create a Blade view with the raw component string content.
     */
    protected function createBladeViewFromString(string $contents): string
    {
        if (! is_file($viewFile = Blade::config('config.cache_path') . '/' . sha1($contents) . '.blade.php')) {
            $container = ApplicationContext::getContainer();
            $filesystem = $container->get(Filesystem::class);
            $filesystem->put($viewFile, $contents, true);
        }

        return '__components::' . basename($viewFile, '.blade.php');
    }

    /**
     * Extract the public properties for the component.
     */
    protected function extractPublicProperties(): array
    {
        $class = $this::class;

        if (! isset(static::$propertyCache[$class])) {
            $reflection = new ReflectionClass($this);

            static::$propertyCache[$class] = collect($reflection->getProperties(ReflectionProperty::IS_PUBLIC))
                ->reject(fn (ReflectionProperty $property) => $property->isStatic())
                ->reject(fn (ReflectionProperty $property) => $this->shouldIgnore($property->getName()))
                ->map(fn (ReflectionProperty $property) => $property->getName())->all();
        }

        $values = [];

        foreach (static::$propertyCache[$class] as $property) {
            $values[$property] = $this->{$property};
        }

        return $values;
    }

    /**
     * Extract the public methods for the component.
     */
    protected function extractPublicMethods(): array
    {
        $class = $this::class;

        if (! isset(static::$methodCache[$class])) {
            $reflection = new ReflectionClass($this);

            static::$methodCache[$class] = collect($reflection->getMethods(ReflectionMethod::IS_PUBLIC))
                ->reject(fn (ReflectionMethod $method) => $this->shouldIgnore($method->getName()))
                ->map(fn (ReflectionMethod $method) => $method->getName());
        }

        $values = [];

        foreach (static::$methodCache[$class] as $method) {
            $values[$method] = $this->createVariableFromMethod(new ReflectionMethod($this, $method));
        }

        return $values;
    }

    /**
     * Create a callable variable from the given method.
     *
     * @return mixed
     */
    protected function createVariableFromMethod(ReflectionMethod $method)
    {
        return $method->getNumberOfParameters() === 0
            ? $this->createInvokableVariable($method->getName())
            : Closure::fromCallable([$this, $method->getName()]);
    }

    /**
     * Create an invokable, toStringable variable for the given component method.
     *
     * @return InvokableComponentVariable
     */
    protected function createInvokableVariable(string $method)
    {
        return new InvokableComponentVariable(fn () => $this->{$method}());
    }

    /**
     * Determine if the given property / method should be ignored.
     *
     * @param string $name
     */
    protected function shouldIgnore($name): bool
    {
        return Str::startsWith($name, '__')
            || in_array($name, $this->ignoredMethods());
    }

    /**
     * Get the methods that should be ignored.
     */
    protected function ignoredMethods(): array
    {
        return array_merge([
            'data',
            'render',
            'resolveView',
            'shouldRender',
            'view',
            'withName',
            'withAttributes',
        ], $this->except);
    }
}

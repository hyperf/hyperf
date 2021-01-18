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
use Hyperf\Utils\Str;
use Hyperf\ViewEngine\Blade;
use Hyperf\ViewEngine\Compiler\CompilerInterface;
use Hyperf\ViewEngine\Compiler\ComponentTagCompiler;
use Hyperf\ViewEngine\View;

class DynamicComponent extends Component
{
    /**
     * The name of the component.
     *
     * @var string
     */
    public $component;

    /**
     * The component tag compiler instance.
     *
     * @var null|ComponentTagCompiler
     */
    protected $compiler;

    /**
     * The cached component classes.
     *
     * @var array
     */
    protected $componentClasses = [];

    /**
     * The cached binding keys for component classes.
     *
     * @var array
     */
    protected $bindings = [];

    /**
     * Create a new component instance.
     */
    public function __construct(string $component)
    {
        $this->component = $component;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return Closure|string|View
     */
    public function render()
    {
        $template = <<<'EOF'
<?php extract(collect($attributes->getAttributes())->mapWithKeys(function ($value, $key) { return [Hyperf\Utils\Str::camel(str_replace(':', ' ', $key)) => $value]; })->all(), EXTR_SKIP); ?>
{{ props }}
<x-{{ component }} {{ bindings }} {{ attributes }}>
{{ slots }}
{{ defaultSlot }}
</x-{{ component }}>
EOF;

        return function ($data) use ($template) {
            $bindings = $this->bindings($class = $this->classForComponent());

            return str_replace(
                [
                    '{{ component }}',
                    '{{ props }}',
                    '{{ bindings }}',
                    '{{ attributes }}',
                    '{{ slots }}',
                    '{{ defaultSlot }}',
                ],
                [
                    $this->component,
                    $this->compileProps($bindings),
                    $this->compileBindings($bindings),
                    class_exists($class) ? '{{ $attributes }}' : '',
                    $this->compileSlots($data['__laravel_slots']),
                    '{{ $slot ?? "" }}',
                ],
                $template
            );
        };
    }

    /**
     * Compile the `@props` directive for the component.
     *
     * @return string
     */
    protected function compileProps(array $bindings)
    {
        if (empty($bindings)) {
            return '';
        }

        return '@props(' . '[\'' . implode('\',\'', collect($bindings)->map(function ($dataKey) {
            return Str::camel($dataKey);
        })->all()) . '\']' . ')';
    }

    /**
     * Compile the bindings for the component.
     *
     * @return string
     */
    protected function compileBindings(array $bindings)
    {
        return collect($bindings)->map(function ($key) {
            return ':' . $key . '="$' . Str::camel(str_replace(':', ' ', $key)) . '"';
        })->implode(' ');
    }

    /**
     * Compile the slots for the component.
     *
     * @return string
     */
    protected function compileSlots(array $slots)
    {
        return collect($slots)->map(function ($slot, $name) {
            return $name === '__default' ? null : '<x-slot name="' . $name . '">{{ $' . $name . ' }}</x-slot>';
        })->filter()->implode(PHP_EOL);
    }

    /**
     * Get the class for the current component.
     *
     * @return string
     */
    protected function classForComponent()
    {
        if (isset($this->componentClasses[$this->component])) {
            return $this->componentClasses[$this->component];
        }

        return $this->componentClasses[$this->component] =
                    $this->compiler()->componentClass($this->component);
    }

    /**
     * Get the names of the variables that should be bound to the component.
     *
     * @return array
     */
    protected function bindings(string $class)
    {
        if (! isset($this->bindings[$class])) {
            [$data, $attributes] = $this->compiler()->partitionDataAndAttributes($class, $this->attributes->getAttributes());

            $this->bindings[$class] = array_keys($data->all());
        }

        return $this->bindings[$class];
    }

    /**
     * Get an instance of the Blade tag compiler.
     *
     * @return ComponentTagCompiler
     */
    protected function compiler()
    {
        if (! $this->compiler) {
            $this->compiler = new ComponentTagCompiler(
                Blade::container()->get(CompilerInterface::class)->getClassComponentAliases(),
                Blade::container()->get(CompilerInterface::class)->getClassComponentNamespaces(),
                Blade::container()->get(CompilerInterface::class),
                Blade::container()->get(CompilerInterface::class)->getComponentAutoload()
            );
        }

        return $this->compiler;
    }
}

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

use Hyperf\Stringable\Str;
use Hyperf\ViewEngine\Blade;
use Hyperf\ViewEngine\Compiler\CompilerInterface;
use Hyperf\ViewEngine\Compiler\ComponentTagCompiler;
use Hyperf\ViewEngine\View;

use function Hyperf\Collection\collect;

class DynamicComponent extends Component
{
    /**
     * The component tag compiler instance.
     */
    protected ?ComponentTagCompiler $compiler = null;

    /**
     * The cached component classes.
     */
    protected array $componentClasses = [];

    /**
     * The cached binding keys for component classes.
     */
    protected array $bindings = [];

    /**
     * Create a new component instance.
     *
     * @param string $component the name of the component
     */
    public function __construct(public string $component)
    {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): mixed
    {
        $template = <<<'EOF'
<?php extract(\Hyperf\Collection\collect($attributes->getAttributes())->mapWithKeys(function ($value, $key) { return [Hyperf\Stringable\Str::camel(str_replace(':', ' ', $key)) => $value]; })->all(), EXTR_SKIP); ?>
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
     */
    protected function compileProps(array $bindings): string
    {
        if (empty($bindings)) {
            return '';
        }

        return '@props([\'' . implode('\',\'', collect($bindings)->map(fn ($dataKey) => Str::camel($dataKey))->all()) . '\'])';
    }

    /**
     * Compile the bindings for the component.
     */
    protected function compileBindings(array $bindings): string
    {
        return collect($bindings)->map(fn ($key) => ':' . $key . '="$' . Str::camel(str_replace(':', ' ', $key)) . '"')->implode(' ');
    }

    /**
     * Compile the slots for the component.
     */
    protected function compileSlots(array $slots): string
    {
        return collect($slots)->map(fn ($slot, $name) => $name === '__default' ? null : '<x-slot name="' . $name . '">{{ $' . $name . ' }}</x-slot>')->filter()->implode(PHP_EOL);
    }

    /**
     * Get the class for the current component.
     */
    protected function classForComponent(): string
    {
        if (isset($this->componentClasses[$this->component])) {
            return $this->componentClasses[$this->component];
        }

        return $this->componentClasses[$this->component] =
                    $this->compiler()->componentClass($this->component);
    }

    /**
     * Get the names of the variables that should be bound to the component.
     */
    protected function bindings(string $class): array
    {
        if (! isset($this->bindings[$class])) {
            [$data, $attributes] = $this->compiler()->partitionDataAndAttributes($class, $this->attributes->getAttributes());

            $this->bindings[$class] = array_keys($data->all());
        }

        return $this->bindings[$class];
    }

    /**
     * Get an instance of the Blade tag compiler.
     */
    protected function compiler(): ComponentTagCompiler
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

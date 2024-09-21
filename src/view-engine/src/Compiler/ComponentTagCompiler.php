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

namespace Hyperf\ViewEngine\Compiler;

use Hyperf\Stringable\Str;
use Hyperf\Support\Filesystem\Filesystem;
use Hyperf\ViewEngine\Blade;
use Hyperf\ViewEngine\Component\AnonymousComponent;
use Hyperf\ViewEngine\Contract\FactoryInterface;
use Hyperf\ViewEngine\Contract\FinderInterface;
use InvalidArgumentException;
use PhpToken;
use ReflectionClass;

use function Hyperf\Collection\collect;

class ComponentTagCompiler
{
    /**
     * The Blade compiler instance.
     */
    protected BladeCompiler $blade;

    /**
     * The "bind:" attributes that have been compiled for the current component.
     */
    protected array $boundAttributes = [];

    protected array $autoloadClasses = [];

    protected array $autoloadComponents = [];

    /**
     * Create new component tag compiler.
     *
     * @param array $aliases the component class aliases
     * @param array $namespaces the component class namespaces
     */
    public function __construct(
        protected array $aliases = [],
        protected array $namespaces = [],
        ?BladeCompiler $blade = null,
        array $autoload = []
    ) {
        $this->autoloadClasses = $autoload['classes'] ?? [null];
        $this->autoloadComponents = $autoload['components'] ?? [null];

        $this->blade = $blade ?: new BladeCompiler(new Filesystem(), sys_get_temp_dir());
    }

    /**
     * Compile the component and slot tags within the given string.
     */
    public function compile(string $value): string
    {
        $value = $this->compileSlots($value);

        return $this->compileTags($value);
    }

    /**
     * Compile the tags within the given string.
     *
     * @throws InvalidArgumentException
     */
    public function compileTags(string $value): string
    {
        $value = $this->compileSelfClosingTags($value);
        $value = $this->compileOpeningTags($value);
        return $this->compileClosingTags($value);
    }

    /**
     * Get the component class for a given component alias.
     *
     * @throws InvalidArgumentException
     */
    public function componentClass(string $component): string
    {
        $viewFactory = Blade::container()->get(FactoryInterface::class);

        if (isset($this->aliases[$component])) {
            if (class_exists($alias = $this->aliases[$component])) {
                return $alias;
            }

            if ($viewFactory->exists($alias)) {
                return $alias;
            }

            throw new InvalidArgumentException(
                "Unable to locate class or view [{$alias}] for component [{$component}]."
            );
        }

        if ($class = $this->findClassByComponent($component)) {
            return $class;
        }

        if ($view = $this->guessComponentFromAutoload($viewFactory, $component)) {
            return $view;
        }

        throw new InvalidArgumentException(
            "Unable to locate a class or view for component [{$component}]."
        );
    }

    /**
     * Guess the view or class name for the given component.
     */
    public function guessComponentFromAutoload(FactoryInterface $viewFactory, string $component): ?string
    {
        foreach ($this->autoloadClasses ?: [null] as $prefix) {
            if (class_exists($view = $this->guessClassName($component, (string) $prefix))) {
                return $view;
            }
        }

        foreach ($this->autoloadComponents ?: [null] as $prefix) {
            if ($viewFactory->exists($view = $this->guessViewName($component, (string) $prefix))) {
                return $view;
            }
        }

        return null;
    }

    /**
     * Find the class for the given component using the registered namespaces.
     */
    public function findClassByComponent(string $component): ?string
    {
        $segments = explode('::', $component);

        $prefix = $segments[0];

        if (! isset($this->namespaces[$prefix]) || ! isset($segments[1])) {
            return null;
        }

        if (class_exists($class = $this->namespaces[$prefix] . '\\' . $this->formatClassName($segments[1]))) {
            return $class;
        }

        return null;
    }

    /**
     * Guess the class name for the given component.
     */
    public function guessClassName(string $component, string $prefix = ''): string
    {
        $class = $this->formatClassName($component);

        if (! $prefix) {
            $prefix = 'App\View\Component\\';
        }

        return rtrim($prefix, '\\') . '\\' . $class;
    }

    /**
     * Format the class name for the given component.
     */
    public function formatClassName(string $component): string
    {
        $componentPieces = array_map(fn ($componentPiece) => ucfirst(Str::camel($componentPiece)), explode('.', $component));

        return implode('\\', $componentPieces);
    }

    /**
     * Guess the view name for the given component.
     */
    public function guessViewName(string $name, string $prefix = ''): string
    {
        $prefix = $prefix ?: 'components.';

        $delimiter = FinderInterface::HINT_PATH_DELIMITER;

        if (Str::contains($name, $delimiter)) {
            return Str::replaceFirst($delimiter, $delimiter . $prefix, $name);
        }

        return $prefix . $name;
    }

    /**
     * Partition the data and extra attributes from the given array of attributes.
     */
    public function partitionDataAndAttributes(string $class, array $attributes): array
    {
        // If the class doesn't exists, we'll assume it's a class-less component and
        // return all of the attributes as both data and attributes since we have
        // now way to partition them. The user can exclude attributes manually.
        if (! class_exists($class)) {
            return [collect($attributes), collect($attributes)];
        }

        $constructor = (new ReflectionClass($class))->getConstructor();

        $parameterNames = $constructor
            ? collect($constructor->getParameters())->map->getName()->all() // @phpstan-ignore method.nonObject
            : [];

        return collect($attributes)->partition(fn ($value, $key) => in_array(Str::camel($key), $parameterNames))->all();
    }

    /**
     * Compile the slot tags within the given string.
     */
    public function compileSlots(string $value): string
    {
        $value = preg_replace_callback('/<\s*x[\-\:]slot\s+(:?)name=(?<name>(\"[^\"]+\"|\\\'[^\\\']+\\\'|[^\s>]+))\s*>/', function ($matches) {
            $name = $this->stripQuotes($matches['name']);

            if ($matches[1] !== ':') {
                $name = "'{$name}'";
            }

            return " @slot({$name}) ";
        }, $value);

        return preg_replace('/<\/\s*x[\-\:]slot[^>]*>/', ' @endslot', $value);
    }

    /**
     * Strip any quotes from the given string.
     */
    public function stripQuotes(string $value): string
    {
        return Str::startsWith($value, ['"', '\''])
            ? substr($value, 1, -1)
            : $value;
    }

    /**
     * Compile the opening tags within the given string.
     *
     * @throws InvalidArgumentException
     */
    protected function compileOpeningTags(string $value): string
    {
        $pattern = "/
            <
                \\s*
                x[-\\:]([\\w\\-\\:\\.]*)
                (?<attributes>
                    (?:
                        \\s+
                        (?:
                            (?:
                                \\{\\{\\s*\\\$attributes(?:[^}]+?)?\\s*\\}\\}
                            )
                            |
                            (?:
                                [\\w\\-:.@]+
                                (
                                    =
                                    (?:
                                        \\\"[^\\\"]*\\\"
                                        |
                                        \\'[^\\']*\\'
                                        |
                                        [^\\'\\\"=<>]+
                                    )
                                )?
                            )
                        )
                    )*
                    \\s*
                )
                (?<![\\/=\\-])
            >
        /x";

        return preg_replace_callback($pattern, function (array $matches) {
            $this->boundAttributes = [];

            $attributes = $this->getAttributesFromAttributeString($matches['attributes']);

            return $this->componentString($matches[1], $attributes);
        }, $value);
    }

    /**
     * Compile the self-closing tags within the given string.
     *
     * @throws InvalidArgumentException
     */
    protected function compileSelfClosingTags(string $value): string
    {
        $pattern = "/
            <
                \\s*
                x[-\\:]([\\w\\-\\:\\.]*)
                \\s*
                (?<attributes>
                    (?:
                        \\s+
                        (?:
                            (?:
                                \\{\\{\\s*\\\$attributes(?:[^}]+?)?\\s*\\}\\}
                            )
                            |
                            (?:
                                [\\w\\-:.@]+
                                (
                                    =
                                    (?:
                                        \\\"[^\\\"]*\\\"
                                        |
                                        \\'[^\\']*\\'
                                        |
                                        [^\\'\\\"=<>]+
                                    )
                                )?
                            )
                        )
                    )*
                    \\s*
                )
            \\/>
        /x";

        return preg_replace_callback($pattern, function (array $matches) {
            $this->boundAttributes = [];

            $attributes = $this->getAttributesFromAttributeString($matches['attributes']);

            return $this->componentString($matches[1], $attributes) . "\n@endcomponentClass ";
        }, $value);
    }

    /**
     * Compile the Blade component string for the given component and attributes.
     *
     * @throws InvalidArgumentException
     */
    protected function componentString(string $component, array $attributes): string
    {
        $class = $this->componentClass($component);

        [$data, $attributes] = $this->partitionDataAndAttributes($class, $attributes);

        $data = $data->mapWithKeys(fn ($value, $key) => [Str::camel($key) => $value]);

        // If the component doesn't exists as a class we'll assume it's a class-less
        // component and pass the component as a view parameter to the data so it
        // can be accessed within the component and we can render out the view.
        if (! class_exists($class)) {
            $parameters = [
                'view' => "'{$class}'",
                'data' => '[' . $this->attributesToString($data->all(), $escapeBound = false) . ']',
            ];

            $class = AnonymousComponent::class;
        } else {
            $parameters = $data->all();
        }

        return " @component('{$class}', '{$component}', [" . $this->attributesToString($parameters, $escapeBound = false) . '])
<?php $component->withAttributes([' . $this->attributesToString($attributes->all()) . ']); ?>';
    }

    /**
     * Compile the closing tags within the given string.
     */
    protected function compileClosingTags(string $value): string
    {
        return preg_replace('/<\/\s*x[-\:][\w\-\:\.]*\s*>/', ' @endcomponentClass ', $value);
    }

    /**
     * Get an array of attributes from the given attribute string.
     */
    protected function getAttributesFromAttributeString(string $attributeString): array
    {
        $attributeString = $this->parseAttributeBag($attributeString);

        $attributeString = $this->parseBindAttributes($attributeString);

        $pattern = '/
            (?<attribute>[\w\-:.@]+)
            (
                =
                (?<value>
                    (
                        \"[^\"]+\"
                        |
                        \\\'[^\\\']+\\\'
                        |
                        [^\s>]+
                    )
                )
            )?
        /x';

        if (! preg_match_all($pattern, $attributeString, $matches, PREG_SET_ORDER)) {
            return [];
        }

        return collect($matches)->mapWithKeys(function ($match) {
            $attribute = $match['attribute'];
            $value = $match['value'] ?? null;

            if (is_null($value)) {
                $value = 'true';

                $attribute = Str::start($attribute, 'bind:');
            }

            $value = $this->stripQuotes($value);

            if (Str::startsWith($attribute, 'bind:')) {
                $attribute = Str::after($attribute, 'bind:');

                $this->boundAttributes[$attribute] = true;
            } else {
                $value = "'" . $this->compileAttributeEchos($value) . "'";
            }

            return [$attribute => $value];
        })->toArray();
    }

    /**
     * Parse the attribute bag in a given attribute string into it's fully-qualified syntax.
     */
    protected function parseAttributeBag(string $attributeString): string
    {
        $pattern = '/
            (?:^|\s+)                                        # start of the string or whitespace between attributes
            \{\{\s*(\$attributes(?:[^}]+?(?<!\s))?)\s*\}\} # exact match of attributes variable being echoed
        /x';

        return preg_replace($pattern, ' :attributes="$1"', $attributeString);
    }

    /**
     * Parse the "bind" attributes in a given attribute string into their fully-qualified syntax.
     */
    protected function parseBindAttributes(string $attributeString): string
    {
        $pattern = '/
            (?:^|\s+)     # start of the string or whitespace between attributes
            :             # attribute needs to start with a semicolon
            ([\w\-:.@]+)  # match the actual attribute name
            =             # only match attributes that have a value
        /xm';

        return preg_replace($pattern, ' bind:$1=', $attributeString);
    }

    /**
     * Compile any Blade echo statements that are present in the attribute string.
     *
     * These echo statements need to be converted to string concatenation statements.
     */
    protected function compileAttributeEchos(string $attributeString): string
    {
        $value = $this->blade->compileEchos($attributeString);

        $value = $this->escapeSingleQuotesOutsideOfPhpBlocks($value);

        $value = str_replace('<?php echo ', '\'.', $value);
        return str_replace('; ?>', '.\'', $value);
    }

    /**
     * Escape the single quotes in the given string that are outside of PHP blocks.
     */
    protected function escapeSingleQuotesOutsideOfPhpBlocks(string $value): string
    {
        return collect(PhpToken::tokenize($value))->map(function (PhpToken $token) {
            return $token->id === T_INLINE_HTML ? str_replace("'", "\\'", $token->text) : $token->text;
        })->implode('');
    }

    /**
     * Convert an array of attributes to a string.
     */
    protected function attributesToString(array $attributes, bool $escapeBound = true): string
    {
        return collect($attributes)
            ->map(fn (string $value, string $attribute) => $escapeBound && isset($this->boundAttributes[$attribute]) && $value !== 'true' && ! is_numeric($value)
                ? "'{$attribute}' => \\Hyperf\\ViewEngine\\Compiler\\BladeCompiler::sanitizeComponentAttribute({$value})"
                : "'{$attribute}' => {$value}")
            ->implode(',');
    }
}

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

use Hyperf\Collection\Arr;
use Hyperf\Collection\Collection;
use Hyperf\Stringable\Str;
use InvalidArgumentException;
use PhpToken;

use function Hyperf\Collection\collect;
use function Hyperf\Support\class_basename;

class BladeCompiler extends Compiler implements CompilerInterface
{
    use Concern\CompilesComments;
    use Concern\CompilesComponents;
    use Concern\CompilesConditionals;
    use Concern\CompilesEchos;
    use Concern\CompilesErrors;
    use Concern\CompilesIncludes;
    use Concern\CompilesInjections;
    use Concern\CompilesJson;
    use Concern\CompilesLayouts;
    use Concern\CompilesLoops;
    use Concern\CompilesRawPhp;
    use Concern\CompilesStacks;
    use Concern\CompilesTranslations;

    /**
     * All the registered extensions.
     */
    protected array $extensions = [];

    /**
     * All custom "directive" handlers.
     */
    protected array $customDirectives = [];

    /**
     * All custom "condition" handlers.
     */
    protected array $conditions = [];

    /**
     * All the registered precompilers.
     */
    protected array $precompilers = [];

    /**
     * The file currently being compiled.
     */
    protected ?string $path = null;

    /**
     * All the available compiler functions.
     */
    protected array $compilers = [
        // 'Comments',
        'Extensions',
        'Statements',
        'Echos',
    ];

    /**
     * Array of opening and closing tags for raw echos.
     */
    protected array $rawTags = ['{!!', '!!}'];

    /**
     * Array of opening and closing tags for regular echos.
     */
    protected array $contentTags = ['{{', '}}'];

    /**
     * Array of opening and closing tags for escaped echos.
     */
    protected array $escapedTags = ['{{{', '}}}'];

    /**
     * The "regular" / legacy echo string format.
     */
    protected string $echoFormat = '\Hyperf\ViewEngine\T::e(%s)';

    /**
     * Array of footer lines to be added to template.
     */
    protected array $footer = [];

    /**
     * Array to temporary store the raw blocks found in the template.
     */
    protected array $rawBlocks = [];

    /**
     * The array of class component aliases and their class names.
     */
    protected array $classComponentAliases = [];

    /**
     * The array of class component namespaces to autoload from.
     */
    protected array $classComponentNamespaces = [];

    /**
     * Indicates if component tags should be compiled.
     */
    protected bool $compilesComponentTags = true;

    protected array $componentAutoload = [];

    /**
     * Compile the view at the given path.
     */
    public function compile(?string $path = null)
    {
        $path ??= $this->getPath();

        if (! is_null($this->cachePath)) {
            $contents = $this->compileString($this->files->get($path));

            if (! empty($path)) {
                $contents = $this->appendFilePath($contents, $path);
            }

            $this->files->put(
                $this->getCompiledPath($path),
                $contents,
                true
            );
        }
    }

    /**
     * Get the path currently being compiled.
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Set the path currently being compiled.
     */
    public function setPath(string $path)
    {
        $this->path = $path;
    }

    /**
     * Compile the given Blade template contents.
     */
    public function compileString(string $value): string
    {
        [$this->footer, $result] = [[], ''];

        // First we will compile the Blade component tags. This is a precompile style
        // step which compiles the component Blade tags into @component directives
        // that may be used by Blade. Then we should call any other precompilers.
        $value = $this->compileComponentTags(
            $this->compileComments($this->storeUncompiledBlocks($value))
        );

        foreach ($this->precompilers as $precompiler) {
            $value = call_user_func($precompiler, $value);
        }

        // Here we will loop through all the tokens returned by the Zend lexer and
        // parse each one into the corresponding valid PHP. We will then have this
        // template as the correctly rendered PHP that can be rendered natively.
        foreach (PhpToken::tokenize($value) as $token) {
            $result .= $this->parseToken($token);
        }

        if (! empty($this->rawBlocks)) {
            $result = $this->restoreRawContent($result);
        }

        // If there are any footer lines that need to get added to a template we will
        // add them here at the end of the template. This gets used mainly for the
        // template inheritance via the extends keyword that should be appended.
        /* @phpstan-ignore-next-line */
        if (count($this->footer) > 0) {
            $result = $this->addFooters($result);
        }

        return $result;
    }

    /**
     * Strip the parentheses from the given expression.
     */
    public function stripParentheses(string $expression): string
    {
        if (Str::startsWith($expression, '(')) {
            $expression = substr($expression, 1, -1);
        }

        return $expression;
    }

    /**
     * Register a custom Blade compiler.
     */
    public function extend(callable $compiler)
    {
        $this->extensions[] = $compiler;
    }

    /**
     * Get the extensions used by the compiler.
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * Register an "if" statement directive.
     */
    public function if(string $name, callable $callback)
    {
        $this->conditions[$name] = $callback;

        $this->directive($name, fn ($expression) => $expression !== ''
                ? "<?php if (\\Hyperf\\ViewEngine\\Blade::check('{$name}', {$expression})): ?>"
                : "<?php if (\\Hyperf\\ViewEngine\\Blade::check('{$name}')): ?>");

        $this->directive('unless' . $name, fn ($expression) => $expression !== ''
            ? "<?php if (! \\Hyperf\\ViewEngine\\Blade::check('{$name}', {$expression})): ?>"
            : "<?php if (! \\Hyperf\\ViewEngine\\Blade::check('{$name}')): ?>");

        $this->directive('else' . $name, fn ($expression) => $expression !== ''
            ? "<?php elseif (\\Hyperf\\ViewEngine\\Blade::check('{$name}', {$expression})): ?>"
            : "<?php elseif (\\Hyperf\\ViewEngine\\Blade::check('{$name}')): ?>");

        $this->directive('end' . $name, fn () => '<?php endif; ?>');
    }

    /**
     * Check the result of a condition.
     *
     * @param array $parameters
     */
    public function check(string $name, ...$parameters): bool
    {
        return call_user_func($this->conditions[$name], ...$parameters);
    }

    /**
     * Register a class-based component alias directive.
     */
    public function component(string $class, ?string $alias = null, string $prefix = '')
    {
        if (! is_null($alias) && Str::contains($alias, '\\')) {
            [$class, $alias] = [$alias, $class];
        }

        if (is_null($alias)) {
            $alias = Str::contains($class, '\View\Components\\')
                            ? collect(explode('\\', Str::after($class, '\View\Components\\')))->map(fn ($segment) => Str::kebab($segment))->implode(':')
                            : Str::kebab(class_basename($class));
        }

        if (! empty($prefix)) {
            $alias = $prefix . '-' . $alias;
        }

        $this->classComponentAliases[$alias] = $class;
    }

    /**
     * Register an array of class-based components.
     */
    public function components(array $components, string $prefix = '')
    {
        foreach ($components as $key => $value) {
            if (is_numeric($key)) {
                static::component($value, null, $prefix);
            } else {
                static::component($key, $value, $prefix);
            }
        }
    }

    /**
     * Get the registered class component aliases.
     */
    public function getClassComponentAliases(): array
    {
        return $this->classComponentAliases;
    }

    /**
     * Register a class-based component namespace.
     */
    public function componentNamespace(string $namespace, string $prefix)
    {
        $this->classComponentNamespaces[$prefix] = $namespace;
    }

    /**
     * Get the registered class component namespaces.
     */
    public function getClassComponentNamespaces(): array
    {
        return $this->classComponentNamespaces;
    }

    /**
     * Get component autoload config.
     */
    public function getComponentAutoload(): array
    {
        return $this->componentAutoload;
    }

    /**
     * Set component autoload config.
     */
    public function setComponentAutoload(array $config)
    {
        $this->componentAutoload = $config;
    }

    /**
     * Register a component alias directive.
     */
    public function aliasComponent(string $path, ?string $alias = null)
    {
        $alias = $alias ?: Arr::last(explode('.', $path));

        $this->directive($alias, fn ($expression) => $expression
                    ? "<?php \$__env->startComponent('{$path}', {$expression}); ?>"
                    : "<?php \$__env->startComponent('{$path}'); ?>");

        $this->directive('end' . $alias, fn ($expression) => '<?php echo $__env->renderComponent(); ?>');
    }

    /**
     * Register an included alias directive.
     */
    public function include(string $path, ?string $alias = null)
    {
        return $this->aliasInclude($path, $alias);
    }

    /**
     * Register an included alias directive.
     */
    public function aliasInclude(string $path, ?string $alias = null)
    {
        $alias = $alias ?: Arr::last(explode('.', $path));

        $this->directive($alias, function ($expression) use ($path) {
            $expression = $this->stripParentheses($expression) ?: '[]';

            return "<?php echo \$__env->make('{$path}', {$expression}, \\Hyperf\\Collection\\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>";
        });
    }

    /**
     * Register a handler for custom directives.
     *
     * @throws InvalidArgumentException
     */
    public function directive(string $name, callable $handler)
    {
        if (! preg_match('/^\w+(?:::\w+)?$/x', $name)) {
            throw new InvalidArgumentException("The directive name [{$name}] is not valid. Directive names must only contain alphanumeric characters and underscores.");
        }

        $this->customDirectives[$name] = $handler;
    }

    /**
     * Get the list of custom directives.
     */
    public function getCustomDirectives(): array
    {
        return $this->customDirectives;
    }

    /**
     * Register a new precompiler.
     */
    public function precompiler(callable $precompiler)
    {
        $this->precompilers[] = $precompiler;
    }

    /**
     * Set the echo format to be used by the compiler.
     */
    public function setEchoFormat(string $format)
    {
        $this->echoFormat = $format;
    }

    /**
     * Set the "echo" format to double encode entities.
     */
    public function withDoubleEncoding()
    {
        $this->setEchoFormat('\Hyperf\ViewEngine\T::e(%s, true)');
    }

    /**
     * Set the "echo" format to not double encode entities.
     */
    public function withoutDoubleEncoding()
    {
        $this->setEchoFormat('\Hyperf\ViewEngine\T::e(%s, false)');
    }

    /**
     * Indicate that component tags should not be compiled.
     */
    public function withoutComponentTags()
    {
        $this->compilesComponentTags = false;
    }

    /**
     * Append the file path to the compiled string.
     */
    protected function appendFilePath(string $contents, string $path): string
    {
        $tokens = $this->getOpenAndClosingPhpTokens($contents);

        if ($tokens->isNotEmpty() && $tokens->last() !== T_CLOSE_TAG) {
            $contents .= ' ?>';
        }

        return $contents . "<?php /**PATH {$path} ENDPATH**/ ?>";
    }

    /**
     * Get the open and closing PHP tag tokens from the given string.
     */
    protected function getOpenAndClosingPhpTokens(string $contents): Collection
    {
        return collect(PhpToken::tokenize($contents))
            ->pluck('0')
            ->filter(fn ($token) => in_array($token, [T_OPEN_TAG, T_OPEN_TAG_WITH_ECHO, T_CLOSE_TAG]));
    }

    /**
     * Store the blocks that do not receive compilation.
     */
    protected function storeUncompiledBlocks(string $value): string
    {
        if (str_contains($value, '@verbatim')) {
            $value = $this->storeVerbatimBlocks($value);
        }

        if (str_contains($value, '@php')) {
            $value = $this->storePhpBlocks($value);
        }

        return $value;
    }

    /**
     * Store the verbatim blocks and replace them with a temporary placeholder.
     */
    protected function storeVerbatimBlocks(string $value): string
    {
        return preg_replace_callback('/(?<!@)@verbatim(.*?)@endverbatim/s', fn ($matches) => $this->storeRawBlock($matches[1]), $value);
    }

    /**
     * Store the PHP blocks and replace them with a temporary placeholder.
     */
    protected function storePhpBlocks(string $value): string
    {
        return preg_replace_callback('/(?<!@)@php(.*?)@endphp/s', fn ($matches) => $this->storeRawBlock("<?php{$matches[1]}?>"), $value);
    }

    /**
     * Store a raw block and return a unique raw placeholder.
     */
    protected function storeRawBlock(string $value): string
    {
        return $this->getRawPlaceholder(
            array_push($this->rawBlocks, $value) - 1
        );
    }

    /**
     * Compile the component tags.
     */
    protected function compileComponentTags(string $value): string
    {
        if (! $this->compilesComponentTags) {
            return $value;
        }

        return (new ComponentTagCompiler(
            $this->classComponentAliases,
            $this->classComponentNamespaces,
            $this,
            $this->getComponentAutoload() ?: []
        ))->compile($value);
    }

    /**
     * Replace the raw placeholders with the original code stored in the raw blocks.
     */
    protected function restoreRawContent(string $result): string
    {
        $result = preg_replace_callback('/' . $this->getRawPlaceholder('(\d+)') . '/', fn ($matches) => $this->rawBlocks[$matches[1]], $result);

        $this->rawBlocks = [];

        return $result;
    }

    /**
     * Get a placeholder to temporary mark the position of raw blocks.
     */
    protected function getRawPlaceholder(int|string $replace): string
    {
        return str_replace('#', (string) $replace, '@__raw_block_#__@');
    }

    /**
     * Add the stored footers onto the given content.
     */
    protected function addFooters(string $result): string
    {
        return ltrim($result, "\n")
                . "\n" . implode("\n", array_reverse($this->footer));
    }

    /**
     * Parse the tokens from the template.
     */
    protected function parseToken(PhpToken $token): string
    {
        $id = $token->id;
        $content = $token->text;

        if ($id === T_INLINE_HTML) {
            foreach ($this->compilers as $type) {
                $content = $this->{"compile{$type}"}($content);
            }
        }

        return $content;
    }

    /**
     * Execute the user defined extensions.
     */
    protected function compileExtensions(string $value): string
    {
        foreach ($this->extensions as $compiler) {
            $value = $compiler($value, $this);
        }

        return $value;
    }

    /**
     * Compile Blade statements that start with "@".
     */
    protected function compileStatements(string $template): string
    {
        preg_match_all('/\B@(@?\w+(?:::\w+)?)([ \t]*)(\( ( [\S\s]*? ) \))?/x', $template, $matches);

        $offset = 0;

        for ($i = 0; isset($matches[0][$i]); ++$i) {
            $match = [
                $matches[0][$i],
                $matches[1][$i],
                $matches[2][$i],
                $matches[3][$i] ?: null,
                $matches[4][$i] ?: null,
            ];

            // Here we check to see if we have properly found the closing parenthesis by
            // regex pattern or not, and will recursively continue on to the next ")"
            // then check again until the tokenizer confirms we find the right one.
            while (isset($match[4])
                && Str::endsWith($match[0], ')')
                && ! $this->hasEvenNumberOfParentheses($match[0])) {
                $rest = Str::before(Str::after($template, $match[0]), ')');

                $match[0] = $match[0] . $rest . ')';
                $match[3] = $match[3] . $rest . ')';
                $match[4] = $match[4] . $rest;
            }

            [$template, $offset] = $this->replaceFirstStatement(
                $match[0],
                $this->compileStatement($match),
                $template,
                $offset
            );
        }

        return $template;
    }

    /**
     * Determine if the given expression has the same number of opening and closing parentheses.
     */
    protected function hasEvenNumberOfParentheses(string $expression): bool
    {
        $tokens = token_get_all('<?php ' . $expression);

        if (Arr::last($tokens) !== ')') {
            return false;
        }

        $opening = 0;
        $closing = 0;

        foreach ($tokens as $token) {
            if ($token == ')') {
                ++$closing;
            } elseif ($token == '(') {
                ++$opening;
            }
        }

        return $opening === $closing;
    }

    /**
     * Replace the first match for a statement compilation operation.
     */
    protected function replaceFirstStatement(string $search, string $replace, string $subject, int $offset): array
    {
        if ($search === '') {
            return [$subject, $offset];
        }

        $position = strpos($subject, $search, $offset);

        if ($position !== false) {
            return [
                substr_replace($subject, $replace, $position, strlen($search)),
                $position + strlen($replace),
            ];
        }

        return [$subject, 0];
    }

    /**
     * Compile a single Blade @ statement.
     */
    protected function compileStatement(array $match): string
    {
        if (Str::contains($match[1], '@')) {
            $match[0] = isset($match[3]) ? $match[1] . $match[3] : $match[1];
        } elseif (isset($this->customDirectives[$match[1]])) {
            $match[0] = $this->callCustomDirective($match[1], Arr::get($match, 3));
        } elseif (method_exists($this, $method = 'compile' . ucfirst($match[1]))) {
            $match[0] = $this->{$method}(Arr::get($match, 3));
        } else {
            return $match[0];
        }

        return isset($match[3]) ? $match[0] : $match[0] . $match[2];
    }

    /**
     * Call the given directive with the given value.
     */
    protected function callCustomDirective(string $name, ?string $value): string
    {
        $value ??= '';
        if (Str::startsWith($value, '(') && Str::endsWith($value, ')')) {
            $value = Str::substr($value, 1, -1);
        }

        return call_user_func($this->customDirectives[$name], trim($value));
    }
}

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
namespace Hyperf\Translation;

use Countable;
use Hyperf\Contract\TranslatorInterface;
use Hyperf\Contract\TranslatorLoaderInterface;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Collection;
use Hyperf\Utils\Context;
use Hyperf\Utils\Str;
use Hyperf\Utils\Traits\Macroable;

class Translator implements TranslatorInterface
{
    use Macroable;

    /**
     * The loader implementation.
     *
     * @var TranslatorLoaderInterface
     */
    protected $loader;

    /**
     * The default locale being used by the translator.
     *
     * @var string
     */
    protected $locale;

    /**
     * The fallback locale used by the translator.
     *
     * @var string
     */
    protected $fallback;

    /**
     * The array of loaded translation groups.
     *
     * @var array
     */
    protected $loaded = [];

    /**
     * The message selector.
     *
     * @var \Hyperf\Translation\MessageSelector
     */
    protected $selector;

    /**
     * A cache of the parsed items.
     *
     * @var array
     */
    protected $parsed = [];

    public function __construct(TranslatorLoaderInterface $loader, string $locale)
    {
        $this->loader = $loader;
        $this->locale = $locale;
    }

    /**
     * Determine if a translation exists for a given locale.
     */
    public function hasForLocale(string $key, ?string $locale = null): bool
    {
        return $this->has($key, $locale, false);
    }

    /**
     * Determine if a translation exists.
     */
    public function has(string $key, ?string $locale = null, bool $fallback = true): bool
    {
        return $this->get($key, [], $locale, $fallback) !== $key;
    }

    /**
     * Get the translation for a given key.
     *
     * @return array|string
     */
    public function trans(string $key, array $replace = [], ?string $locale = null)
    {
        return $this->get($key, $replace, $locale);
    }

    /**
     * Get the translation for the given key.
     *
     * @return array|string
     */
    public function get(string $key, array $replace = [], ?string $locale = null, bool $fallback = true)
    {
        [$namespace, $group, $item] = $this->parseKey($key);

        // Here we will get the locale that should be used for the language line. If one
        // was not passed, we will use the default locales which was given to us when
        // the translator was instantiated. Then, we can load the lines and return.
        $locales = $fallback ? $this->localeArray($locale)
            : [$locale ?: $this->locale()];

        foreach ($locales as $locale) {
            if (! is_null($line = $this->getLine(
                $namespace,
                $group,
                $locale,
                $item,
                $replace
            ))) {
                break;
            }
        }

        // If the line doesn't exist, we will return back the key which was requested as
        // that will be quick to spot in the UI if language keys are wrong or missing
        // from the application's language files. Otherwise we can return the line.
        return $line ?? $key;
    }

    /**
     * Get the translation for a given key from the JSON translation files.
     *
     * @return array|string
     */
    public function getFromJson(string $key, array $replace = [], ?string $locale = null)
    {
        $locale = $locale ?: $this->locale();

        // For JSON translations, there is only one file per locale, so we will simply load
        // that file and then we will be ready to check the array for the key. These are
        // only one level deep so we do not need to do any fancy searching through it.
        $this->load('*', '*', $locale);

        $line = $this->loaded['*']['*'][$locale][$key] ?? null;

        // If we can't find a translation for the JSON key, we will attempt to translate it
        // using the typical translation file. This way developers can always just use a
        // helper such as __ instead of having to pick between trans or __ with views.
        if (! isset($line)) {
            $fallback = $this->get($key, $replace, $locale);

            if ($fallback !== $key) {
                return $fallback;
            }
        }

        return $this->makeReplacements($line ?: $key, $replace);
    }

    /**
     * Get a translation according to an integer value.
     *
     * @param array|\Countable|int $number
     */
    public function transChoice(string $key, $number, array $replace = [], ?string $locale = null): string
    {
        return $this->choice($key, $number, $replace, $locale);
    }

    /**
     * Get a translation according to an integer value.
     *
     * @param array|\Countable|int $number
     */
    public function choice(string $key, $number, array $replace = [], ?string $locale = null): string
    {
        $line = $this->get(
            $key,
            $replace,
            $locale = $this->localeForChoice($locale)
        );

        // If the given "number" is actually an array or countable we will simply count the
        // number of elements in an instance. This allows developers to pass an array of
        // items without having to count it on their end first which gives bad syntax.
        if (is_array($number) || $number instanceof Countable) {
            $number = count($number);
        }

        $replace['count'] = $number;

        return $this->makeReplacements(
            $this->getSelector()->choose($line, $number, $locale),
            $replace
        );
    }

    /**
     * Add translation lines to the given locale.
     */
    public function addLines(array $lines, string $locale, string $namespace = '*')
    {
        foreach ($lines as $key => $value) {
            [$group, $item] = explode('.', $key, 2);

            Arr::set($this->loaded, "{$namespace}.{$group}.{$locale}.{$item}", $value);
        }
    }

    /**
     * Load the specified language group.
     */
    public function load(string $namespace, string $group, string $locale)
    {
        if ($this->isLoaded($namespace, $group, $locale)) {
            return;
        }

        // The loader is responsible for returning the array of language lines for the
        // given namespace, group, and locale. We'll set the lines in this array of
        // lines that have already been loaded so that we can easily access them.
        $lines = $this->loader->load($locale, $group, $namespace);

        $this->loaded[$namespace][$group][$locale] = $lines;
    }

    /**
     * Add a new namespace to the loader.
     */
    public function addNamespace(string $namespace, string $hint)
    {
        $this->loader->addNamespace($namespace, $hint);
    }

    /**
     * Add a new JSON path to the loader.
     */
    public function addJsonPath(string $path)
    {
        $this->loader->addJsonPath($path);
    }

    /**
     * Parse a key into namespace, group, and item.
     */
    public function parseKey(string $key): array
    {
        // If we've already parsed the given key, we'll return the cached version we
        // already have, as this will save us some processing. We cache off every
        // key we parse so we can quickly return it on all subsequent requests.
        if (isset($this->parsed[$key])) {
            return $this->parsed[$key];
        }

        // If the key does not contain a double colon, it means the key is not in a
        // namespace, and is just a regular configuration item. Namespaces are a
        // tool for organizing configuration items for things such as modules.
        if (strpos($key, '::') === false) {
            $segments = explode('.', $key);

            $parsed = $this->parseBasicSegments($segments);
        } else {
            $parsed = $this->parseNamespacedSegments($key);
        }

        if (is_null($parsed[0])) {
            $parsed[0] = '*';
        }

        // Once we have the parsed array of this key's elements, such as its groups
        // and namespace, we will cache each array inside a simple list that has
        // the key and the parsed array for quick look-ups for later requests.
        return $this->parsed[$key] = $parsed;
    }

    /**
     * Get the message selector instance.
     */
    public function getSelector(): MessageSelector
    {
        if (! isset($this->selector)) {
            $this->selector = new MessageSelector();
        }

        return $this->selector;
    }

    /**
     * Set the message selector instance.
     */
    public function setSelector(MessageSelector $selector)
    {
        $this->selector = $selector;
    }

    /**
     * Get the language line loader implementation.
     *
     * @return TranslatorLoaderInterface
     */
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * Get the default locale being used.
     */
    public function locale(): string
    {
        return $this->getLocale();
    }

    /**
     * Get the context locale key.
     */
    public function getLocaleContextKey(): string
    {
        return sprintf('%s::%s', TranslatorInterface::class, 'locale');
    }

    /**
     * Get the default locale being used.
     */
    public function getLocale(): string
    {
        $locale = Context::get($this->getLocaleContextKey());

        return (string) ($locale ?? $this->locale);
    }

    /**
     * Set the default locale.
     */
    public function setLocale(string $locale)
    {
        Context::set($this->getLocaleContextKey(), $locale);
    }

    /**
     * Get the fallback locale being used.
     */
    public function getFallback(): string
    {
        return $this->fallback;
    }

    /**
     * Set the fallback locale being used.
     */
    public function setFallback(string $fallback)
    {
        $this->fallback = $fallback;
    }

    /**
     * Set the loaded translation groups.
     */
    public function setLoaded(array $loaded)
    {
        $this->loaded = $loaded;
    }

    /**
     * Set the parsed value of a key.
     */
    public function setParsedKey(string $key, array $parsed)
    {
        $this->parsed[$key] = $parsed;
    }

    /**
     * Get the proper locale for a choice operation.
     */
    protected function localeForChoice(?string $locale): string
    {
        return $locale ?: $this->locale() ?: $this->fallback;
    }

    /**
     * Retrieve a language line out the loaded array.
     *
     * @param mixed $item
     * @return null|array|string
     */
    protected function getLine(string $namespace, string $group, string $locale, $item, array $replace)
    {
        $this->load($namespace, $group, $locale);
        if (! is_null($item)) {
            $line = Arr::get($this->loaded[$namespace][$group][$locale], $item);
        } else {
            // do for hyperf Arr::get
            $line = $this->loaded[$namespace][$group][$locale];
        }

        if (is_string($line)) {
            return $this->makeReplacements($line, $replace);
        }

        if (is_array($line) && count($line) > 0) {
            foreach ($line as $key => $value) {
                $line[$key] = $this->makeReplacements($value, $replace);
            }

            return $line;
        }

        return null;
    }

    /**
     * Make the place-holder replacements on a line.
     *
     * @param array|string $line
     * @return array|string
     */
    protected function makeReplacements($line, array $replace)
    {
        if (empty($replace)) {
            return $line;
        }

        $replace = $this->sortReplacements($replace);

        foreach ($replace as $key => $value) {
            $key = (string) $key;
            $value = (string) $value;
            $line = str_replace(
                [':' . $key, ':' . Str::upper($key), ':' . Str::ucfirst($key)],
                [$value, Str::upper($value), Str::ucfirst($value)],
                $line
            );
        }

        return $line;
    }

    /**
     * Sort the replacements array.
     */
    protected function sortReplacements(array $replace): array
    {
        return (new Collection($replace))->sortBy(function ($value, $key) {
            return mb_strlen((string) $key) * -1;
        })->all();
    }

    /**
     * Determine if the given group has been loaded.
     */
    protected function isLoaded(string $namespace, string $group, string $locale): bool
    {
        return isset($this->loaded[$namespace][$group][$locale]);
    }

    /**
     * Get the array of locales to be checked.
     */
    protected function localeArray(?string $locale): array
    {
        return array_filter([$locale ?: $this->locale(), $this->fallback]);
    }

    /**
     * Parse an array of basic segments.
     */
    protected function parseBasicSegments(array $segments): array
    {
        // The first segment in a basic array will always be the group, so we can go
        // ahead and grab that segment. If there is only one total segment we are
        // just pulling an entire group out of the array and not a single item.
        $group = $segments[0];

        // If there is more than one segment in this group, it means we are pulling
        // a specific item out of a group and will need to return this item name
        // as well as the group so we know which item to pull from the arrays.
        $item = count($segments) === 1
            ? null
            : implode('.', array_slice($segments, 1));

        return [null, $group, $item];
    }

    /**
     * Parse an array of namespaced segments.
     */
    protected function parseNamespacedSegments(string $key): array
    {
        [$namespace, $item] = explode('::', $key);

        // First we'll just explode the first segment to get the namespace and group
        // since the item should be in the remaining segments. Once we have these
        // two pieces of data we can proceed with parsing out the item's value.
        $itemSegments = explode('.', $item);

        $groupAndItem = array_slice(
            $this->parseBasicSegments($itemSegments),
            1
        );

        return array_merge([$namespace], $groupAndItem);
    }
}

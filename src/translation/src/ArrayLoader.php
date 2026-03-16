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

use Hyperf\Contract\TranslatorLoaderInterface;

class ArrayLoader implements TranslatorLoaderInterface
{
    /**
     * All of the translation messages.
     */
    protected array $messages = [];

    /**
     * Load the messages for the given locale.
     */
    public function load(string $locale, string $group, ?string $namespace = null): array
    {
        $namespace = $namespace ?: '*';

        return $this->messages[$namespace][$locale][$group] ?? [];
    }

    /**
     * Add a new namespace to the loader.
     */
    public function addNamespace(string $namespace, string $hint)
    {
    }

    /**
     * Add a new JSON path to the loader.
     */
    public function addJsonPath(string $path)
    {
    }

    /**
     * Add messages to the loader.
     */
    public function addMessages(string $locale, string $group, array $messages, ?string $namespace = null): self
    {
        $namespace = $namespace ?: '*';

        $this->messages[$namespace][$locale][$group] = $messages;

        return $this;
    }

    /**
     * Get an array of all the registered namespaces.
     */
    public function namespaces(): array
    {
        return [];
    }
}

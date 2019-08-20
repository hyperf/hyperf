<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Translation;

use Hyperf\Translation\Contracts\Loader;

class ArrayLoader implements Loader
{
    /**
     * All of the translation messages.
     *
     * @var array
     */
    protected $messages = [];

    /**
     * Load the messages for the given locale.
     *
     * @param string      $locale
     * @param string      $group
     * @param null|string $namespace
     * @return array
     */
    public function load(string $locale, string $group, $namespace = null): array
    {
        $namespace = $namespace ?: '*';

        return $this->messages[$namespace][$locale][$group] ?? [];
    }

    /**
     * Add a new namespace to the loader.
     *
     * @param string $namespace
     * @param string $hint
     */
    public function addNamespace(string $namespace, string $hint)
    {
    }

    /**
     * Add a new JSON path to the loader.
     *
     * @param string $path
     */
    public function addJsonPath(string $path)
    {
    }

    /**
     * Add messages to the loader.
     *
     * @param string      $locale
     * @param string      $group
     * @param array       $messages
     * @param null|string $namespace
     * @return $this
     */
    public function addMessages(string $locale, string $group, array $messages, $namespace = null)
    {
        $namespace = $namespace ?: '*';

        $this->messages[$namespace][$locale][$group] = $messages;

        return $this;
    }

    /**
     * Get an array of all the registered namespaces.
     *
     * @return array
     */
    public function namespaces(): array
    {
        return [];
    }
}

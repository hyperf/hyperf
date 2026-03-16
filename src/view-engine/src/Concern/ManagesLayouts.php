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

namespace Hyperf\ViewEngine\Concern;

use Hyperf\ViewEngine\Contract\ViewInterface;
use Hyperf\ViewEngine\T;
use InvalidArgumentException;

trait ManagesLayouts
{
    /**
     * All the finished, captured sections.
     */
    protected array $sections = [];

    /**
     * The stack of in-progress sections.
     */
    protected array $sectionStack = [];

    /**
     * The parent placeholder for the request.
     */
    protected static array $parentPlaceholder = [];

    /**
     * Start injecting content into a section.
     *
     * @param string $section
     * @param null|string|ViewInterface $content
     */
    public function startSection($section, $content = null)
    {
        if ($content === null) {
            if (ob_start()) {
                $this->sectionStack[] = $section;
            }
        } else {
            $this->extendSection($section, $content instanceof ViewInterface ? $content : T::e($content));
        }
    }

    /**
     * Inject inline content into a section.
     *
     * @param string $section
     * @param string $content
     */
    public function inject($section, $content)
    {
        $this->startSection($section, $content);
    }

    /**
     * Stop injecting content into a section and return its contents.
     *
     * @return string
     */
    public function yieldSection()
    {
        if (empty($this->sectionStack)) {
            return '';
        }

        return $this->yieldContent($this->stopSection());
    }

    /**
     * Stop injecting content into a section.
     *
     * @param bool $overwrite
     * @return string
     * @throws InvalidArgumentException
     */
    public function stopSection($overwrite = false)
    {
        if (empty($this->sectionStack)) {
            throw new InvalidArgumentException('Cannot end a section without first starting one.');
        }

        $last = array_pop($this->sectionStack);

        if ($overwrite) {
            $this->sections[$last] = ob_get_clean();
        } else {
            $this->extendSection($last, ob_get_clean());
        }

        return $last;
    }

    /**
     * Stop injecting content into a section and append it.
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public function appendSection()
    {
        if (empty($this->sectionStack)) {
            throw new InvalidArgumentException('Cannot end a section without first starting one.');
        }

        $last = array_pop($this->sectionStack);

        if (isset($this->sections[$last])) {
            $this->sections[$last] .= ob_get_clean();
        } else {
            $this->sections[$last] = ob_get_clean();
        }

        return $last;
    }

    /**
     * Get the string contents of a section.
     *
     * @param string $section
     * @return string
     */
    public function yieldContent($section, string|ViewInterface $default = '')
    {
        $sectionContent = $default instanceof ViewInterface ? $default : T::e($default);

        if (isset($this->sections[$section])) {
            $sectionContent = $this->sections[$section];
        }

        $sectionContent = str_replace('@@parent', '--parent--holder--', $sectionContent);

        return str_replace(
            '--parent--holder--',
            '@parent',
            str_replace(static::parentPlaceholder($section), '', $sectionContent)
        );
    }

    /**
     * Get the parent placeholder for the current request.
     *
     * @param string $section
     * @return string
     */
    public static function parentPlaceholder($section = '')
    {
        if (! isset(static::$parentPlaceholder[$section])) {
            static::$parentPlaceholder[$section] = '##parent-placeholder-' . sha1($section) . '##';
        }

        return static::$parentPlaceholder[$section];
    }

    /**
     * Check if section exists.
     *
     * @param string $name
     * @return bool
     */
    public function hasSection($name)
    {
        return array_key_exists($name, $this->sections);
    }

    /**
     * Check if section does not exist.
     *
     * @param string $name
     * @return bool
     */
    public function sectionMissing($name)
    {
        return ! $this->hasSection($name);
    }

    /**
     * Get the contents of a section.
     *
     * @param string $name
     * @param null|string $default
     * @return mixed
     */
    public function getSection($name, $default = null)
    {
        return $this->getSections()[$name] ?? $default;
    }

    /**
     * Get the entire array of sections.
     *
     * @return array
     */
    public function getSections()
    {
        return $this->sections;
    }

    /**
     * Flush all of the sections.
     */
    public function flushSections()
    {
        $this->sections = [];
        $this->sectionStack = [];
    }

    /**
     * Append content to a given section.
     *
     * @param string $section
     * @param string $content
     */
    protected function extendSection($section, $content)
    {
        if (isset($this->sections[$section])) {
            $content = str_replace(static::parentPlaceholder($section), $content, $this->sections[$section]);
        }

        $this->sections[$section] = $content;
    }
}

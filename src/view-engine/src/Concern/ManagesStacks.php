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

use InvalidArgumentException;

use function Hyperf\Tappable\tap;

trait ManagesStacks
{
    /**
     * All the finished, captured push sections.
     */
    protected array $pushes = [];

    /**
     * All the finished, captured prepend sections.
     */
    protected array $prepends = [];

    /**
     * The stack of in-progress push sections.
     */
    protected array $pushStack = [];

    /**
     * Start injecting content into a push section.
     *
     * @param string $section
     * @param string $content
     */
    public function startPush($section, $content = '')
    {
        if ($content === '') {
            if (ob_start()) {
                $this->pushStack[] = $section;
            }
        } else {
            $this->extendPush($section, $content);
        }
    }

    /**
     * Stop injecting content into a push section.
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public function stopPush()
    {
        if (empty($this->pushStack)) {
            throw new InvalidArgumentException('Cannot end a push stack without first starting one.');
        }

        return tap(array_pop($this->pushStack), function ($last) {
            $this->extendPush($last, ob_get_clean());
        });
    }

    /**
     * Start prepending content into a push section.
     *
     * @param string $section
     * @param string $content
     */
    public function startPrepend($section, $content = '')
    {
        if ($content === '') {
            if (ob_start()) {
                $this->pushStack[] = $section;
            }
        } else {
            $this->extendPrepend($section, $content);
        }
    }

    /**
     * Stop prepending content into a push section.
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public function stopPrepend()
    {
        if (empty($this->pushStack)) {
            throw new InvalidArgumentException('Cannot end a prepend operation without first starting one.');
        }

        return tap(array_pop($this->pushStack), function ($last) {
            $this->extendPrepend($last, ob_get_clean());
        });
    }

    /**
     * Get the string contents of a push section.
     *
     * @param string $section
     * @param string $default
     * @return string
     */
    public function yieldPushContent($section, $default = '')
    {
        if (! isset($this->pushes[$section]) && ! isset($this->prepends[$section])) {
            return $default;
        }

        $output = '';

        if (isset($this->prepends[$section])) {
            $output .= implode(array_reverse($this->prepends[$section]));
        }

        if (isset($this->pushes[$section])) {
            $output .= implode($this->pushes[$section]);
        }

        return $output;
    }

    /**
     * Flush all of the stacks.
     */
    public function flushStacks()
    {
        $this->pushes = [];
        $this->prepends = [];
        $this->pushStack = [];
    }

    /**
     * Append content to a given push section.
     *
     * @param string $section
     * @param string $content
     */
    protected function extendPush($section, $content)
    {
        if (! isset($this->pushes[$section])) {
            $this->pushes[$section] = [];
        }

        if (! isset($this->pushes[$section][$this->renderCount])) {
            $this->pushes[$section][$this->renderCount] = $content;
        } else {
            $this->pushes[$section][$this->renderCount] .= $content;
        }
    }

    /**
     * Prepend content to a given stack.
     *
     * @param string $section
     * @param string $content
     */
    protected function extendPrepend($section, $content)
    {
        if (! isset($this->prepends[$section])) {
            $this->prepends[$section] = [];
        }

        if (! isset($this->prepends[$section][$this->renderCount])) {
            $this->prepends[$section][$this->renderCount] = $content;
        } else {
            $this->prepends[$section][$this->renderCount] = $content . $this->prepends[$section][$this->renderCount];
        }
    }
}

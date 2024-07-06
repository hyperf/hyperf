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

use Countable;
use Hyperf\Collection\Arr;
use stdClass;

trait ManagesLoops
{
    /**
     * The stack of in-progress loops.
     */
    protected array $loopsStack = [];

    /**
     * Add new loop to the stack.
     *
     * @param null|array|Countable $data
     */
    public function addLoop($data)
    {
        $length = is_countable($data) ? count($data) : null;

        $parent = Arr::last($this->loopsStack);

        $this->loopsStack[] = [
            'iteration' => 0,
            'index' => 0,
            'remaining' => $length ?? null,
            'count' => $length,
            'first' => true,
            'last' => isset($length) ? $length == 1 : null,
            'odd' => false,
            'even' => true,
            'depth' => count($this->loopsStack) + 1,
            'parent' => $parent ? (object) $parent : null,
        ];
    }

    /**
     * Increment the top loop's indices.
     */
    public function incrementLoopIndices()
    {
        $loop = $this->loopsStack[$index = count($this->loopsStack) - 1];

        $this->loopsStack[$index] = array_merge($this->loopsStack[$index], [
            'iteration' => $loop['iteration'] + 1,
            'index' => $loop['iteration'],
            'first' => $loop['iteration'] == 0,
            'odd' => ! $loop['odd'],
            'even' => ! $loop['even'],
            'remaining' => isset($loop['count']) ? $loop['remaining'] - 1 : null,
            'last' => isset($loop['count']) ? $loop['iteration'] == $loop['count'] - 1 : null,
        ]);
    }

    /**
     * Pop a loop from the top of the loop stack.
     */
    public function popLoop()
    {
        array_pop($this->loopsStack);
    }

    /**
     * Get an instance of the last loop in the stack.
     *
     * @return null|stdClass|void
     */
    public function getLastLoop()
    {
        if ($last = Arr::last($this->loopsStack)) {
            return (object) $last;
        }
    }

    /**
     * Get the entire loop stack.
     *
     * @return array
     */
    public function getLoopStack()
    {
        return $this->loopsStack;
    }
}

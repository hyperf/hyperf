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

namespace Hyperf\ViewEngine\Contract;

interface ViewInterface extends Renderable
{
    /**
     * Get the name of the view.
     */
    public function name(): string;

    /**
     * Add a piece of data to the view.
     *
     * @param array|string $key
     * @param mixed $value
     * @return $this
     */
    public function with($key, $value = null);

    /**
     * Get the array of view data.
     */
    public function getData(): array;
}

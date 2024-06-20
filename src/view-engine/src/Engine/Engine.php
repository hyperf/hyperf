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

namespace Hyperf\ViewEngine\Engine;

abstract class Engine
{
    /**
     * The view that was last to be rendered.
     */
    protected ?string $lastRendered = null;

    /**
     * Get the last view that was rendered.
     */
    public function getLastRendered(): ?string
    {
        return $this->lastRendered;
    }
}

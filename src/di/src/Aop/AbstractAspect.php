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

namespace Hyperf\Di\Aop;

abstract class AbstractAspect implements AroundInterface
{
    /**
     * The classes that you want to weave.
     */
    public array $classes = [];

    /**
     * The annotations that you want to weave.
     */
    public array $annotations = [];

    public ?int $priority = null;
}

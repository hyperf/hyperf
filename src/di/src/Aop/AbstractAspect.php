<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Di\Aop;

abstract class AbstractAspect implements AroundInterface
{
    /**
     * The classes that you want to weaving.
     *
     * @var array
     */
    public $classes = [];

    /**
     * The annotations that you want to weaving.
     *
     * @var array
     */
    public $annotations = [];
}

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

namespace Hyperf\RateLimit\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class RateLimit extends AbstractAnnotation
{
    /**
     * @var int
     */
    public $create = 1;

    /**
     * @var int
     */
    public $consume = 1;

    /**
     * @var int
     */
    public $capacity = 2;

    /**
     * @var callable
     */
    public $limitCallback = [];

    /**
     * @var callable|string
     */
    public $key;

    /**
     * @var int
     */
    public $waitTimeout = 1;
}

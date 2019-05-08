<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\RateLimit\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;
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
    public $create;

    /**
     * @var int
     */
    public $consume;

    /**
     * @var int
     */
    public $capacity;

    /**
     * @var callable
     */
    public $limitCallback;

    /**
     * @var callable|string
     */
    public $key;

    /**
     * @var int
     */
    public $waitTimeout;
}

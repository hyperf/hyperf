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

namespace Hyperf\Task\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Task extends AbstractAnnotation
{
    public function __construct(public int $workerId = -1, public float $timeout = 10)
    {
    }
}

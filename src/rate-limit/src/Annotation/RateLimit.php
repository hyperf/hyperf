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

namespace Hyperf\RateLimit\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class RateLimit extends AbstractAnnotation
{
    public function __construct(
        public ?int $create = null,
        public ?int $consume = null,
        public ?int $capacity = null,
        public mixed $limitCallback = null,
        public mixed $key = null,
        public ?int $waitTimeout = null
    ) {
    }
}

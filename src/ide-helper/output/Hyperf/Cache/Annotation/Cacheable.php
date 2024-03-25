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

namespace Hyperf\Cache\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_METHOD)]
class Cacheable extends AbstractAnnotation
{
    public function __construct(?string $prefix = null, ?string $value = null, ?int $ttl = null, ?string $listener = null, int $offset = 0, string $group = 'default', bool $collect = false)
    {
    }
}

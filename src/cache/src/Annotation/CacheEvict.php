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
class CacheEvict extends AbstractAnnotation
{
    public function __construct(
        public ?string $prefix = null,
        public ?string $value = null,
        public bool $all = false,
        public string $group = 'default',
        public bool $collect = false
    ) {
    }
}

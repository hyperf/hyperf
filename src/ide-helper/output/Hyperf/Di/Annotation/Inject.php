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

namespace Hyperf\Di\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Inject extends AbstractAnnotation
{
    public function __construct(?string $value = null, bool $required = true, bool $lazy = false)
    {
    }
}

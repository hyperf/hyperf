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
namespace Hyperf\Swagger\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractMultipleAnnotation;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class HyperfServer extends AbstractMultipleAnnotation
{
    public function __construct(public string $name)
    {
    }
}

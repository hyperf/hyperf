<?php

declare (strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\ModelListener\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\ModelListener\Collector\ListenerCollector;
use Hyperf\Utils\Arr;
/**
 * @Annotation
 * @Target({"CLASS"})
 */
#[Attribute(Attribute::TARGET_CLASS)]
class ModelListener extends AbstractAnnotation
{
    public function __construct($models)
    {
    }
}
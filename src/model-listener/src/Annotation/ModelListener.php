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

namespace Hyperf\ModelListener\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\ModelListener\Collector\ListenerCollector;

#[Attribute(Attribute::TARGET_CLASS)]
class ModelListener extends AbstractAnnotation
{
    public function __construct(public array $models = [])
    {
    }

    public function collectClass(string $className): void
    {
        parent::collectClass($className);

        foreach ($this->models as $model) {
            ListenerCollector::register($model, $className);
        }
    }
}

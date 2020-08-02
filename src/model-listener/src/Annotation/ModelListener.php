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

use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\ModelListener\Collector\ListenerCollector;
use Hyperf\Utils\Arr;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class ModelListener extends AbstractAnnotation
{
    /**
     * @var array
     */
    public $models = [];

    public function __construct($value = null)
    {
        parent::__construct($value);

        if ($value = $value['value'] ?? null) {
            if (is_string($value)) {
                $this->models = [$value];
            } elseif (is_array($value) && ! Arr::isAssoc($value)) {
                $this->models = $value;
            }
        }
    }

    public function collectClass(string $className): void
    {
        parent::collectClass($className);

        foreach ($this->models as $model) {
            ListenerCollector::register($model, $className);
        }
    }
}

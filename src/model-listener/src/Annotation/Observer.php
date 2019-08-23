<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\ModelListener;

use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\ModelListener\Collector\ObserverCollector;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class Observer extends AbstractAnnotation
{
    /**
     * @var string
     */
    public $model;

    public function collectClass(string $className): void
    {
        parent::collectClass($className);

        ObserverCollector::set($this->model, $className);
    }
}

<?php
declare(strict_types=1);


/**
 *
 * @author zhenguo.guan
 * @date 2019-12-19 10:13
 */

namespace Hyperf\SwooleTable\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class SwooleTable extends AbstractAnnotation
{

    public function collectClass(string $className): void
    {
        parent::collectClass($className);
    }
}
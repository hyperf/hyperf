<?php

declare(strict_types=1);


namespace Hyperf\DbConnection\Annotation;


use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Transactional extends AbstractAnnotation
{

}
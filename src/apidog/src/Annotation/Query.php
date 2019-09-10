<?php
declare(strict_types = 1);
namespace Hyperf\Apidog\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Query extends Param
{
    public $in = 'query';
}

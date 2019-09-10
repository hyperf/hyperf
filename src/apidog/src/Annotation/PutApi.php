<?php
namespace Hyperf\Apidog\Annotation;

use Hyperf\HttpServer\Annotation\Mapping;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class PutApi extends Mapping
{

    public $methods = ['PUT'];
}

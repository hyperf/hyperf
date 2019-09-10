<?php
declare(strict_types = 1);
namespace Hyperf\Apidog\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class FormData extends Param
{
    public $in = 'formData';
}

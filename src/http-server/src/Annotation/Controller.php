<?php

namespace Hyperf\HttpServer\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class Controller extends AbstractAnnotation
{

    /**
     * @var string|null
     */
    public $prefix;

    public function __construct($value = null)
    {
        $this->value = $value;
        if (isset($value['prefix'])) {
            $this->prefix = $value['prefix'];
        }
    }

}
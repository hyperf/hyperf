<?php

namespace Hyperf\Tracer\Annotation;


use Doctrine\Common\Annotations\Annotation\Target;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"ALL"})
 */
class Trace extends AbstractAnnotation
{

    /**
     * @var string
     */
    public $name = '';

    public function __construct($value = null)
    {
        parent::__construct($value);
        if (isset($value['name'])) {
            $this->name = $value['name'];
        }
    }

}
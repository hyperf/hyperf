<?php

namespace Hyperf\Config\Annotation;


use Doctrine\Common\Annotations\Annotation\Target;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Value extends AbstractAnnotation
{

    /**
     * @var string
     */
    public $key;

    public function __construct($value = null)
    {
        parent::__construct($value);
    }

}
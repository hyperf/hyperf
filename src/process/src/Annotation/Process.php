<?php

namespace Hyperf\Process\Annotation;


use Doctrine\Common\Annotations\Annotation\Target;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
class Process extends AbstractAnnotation
{

    /**
     * @var int
     */
    public $nums = 1;

    /**
     * @var string
     */
    public $name = 'hyperf-user-process';

    /**
     * @var bool
     */
    public $redirectStdinStdout = false;

    /**
     * @var int
     */
    public $pipeType = 2;

    /**
     * @var bool
     */
    public $enableCoroutine = true;

}
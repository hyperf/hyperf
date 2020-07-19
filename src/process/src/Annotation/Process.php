<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
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
    public $nums;

    /**
     * @var string
     */
    public $name;

    /**
     * @var bool
     */
    public $redirectStdinStdout;

    /**
     * @var int
     */
    public $pipeType;

    /**
     * @var bool
     */
    public $enableCoroutine;
}

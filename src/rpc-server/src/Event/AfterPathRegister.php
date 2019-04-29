<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\RpcServer\Event;

use Hyperf\RpcServer\Annotation\RpcService as Annotation;

class AfterPathRegister
{
    /**
     * @var string
     */
    public $path;

    /**
     * @var string
     */
    public $className;

    /**
     * @var string
     */
    public $methodName;

    /**
     * @var Annotation
     */
    public $annotation;

    /**
     * @param string $path
     * @param Annotation $annotation
     * @param string $className
     * @param string $methodName
     */
    public function __construct(string $path, string $className, string $methodName, Annotation $annotation)
    {
        $this->path = $path;
        $this->className = $className;
        $this->methodName = $methodName;
        $this->annotation = $annotation;
    }
}

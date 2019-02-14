<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Di\Aop;

use Closure;

class ProceedingJoinPoint
{
    /**
     * @var string
     */
    public $className;

    /**
     * @var string
     */
    public $methodName;

    /**
     * @var mixed[]
     */
    public $arguments;

    /**
     * @var mixed
     */
    public $result;

    /**
     * @var Closure
     */
    public $originalMethod;

    /**
     * @var Closure
     */
    public $pipe;

    public function __construct(Closure $originalMethod, string $className, string $methodName, array $arguments)
    {
        $this->originalMethod = $originalMethod;
        $this->className = $className;
        $this->methodName = $methodName;
        $this->arguments = $arguments;
    }

    public function process()
    {
        $closure = $this->pipe;
        return $closure($this);
    }

    public function processOriginalMethod()
    {
        $this->pipe = null;
        $closure = $this->originalMethod;
        if (count($this->arguments['keys']) > 1) {
            $arguments = value(function () {
                $result = [];
                foreach ($this->arguments['order'] as $order) {
                    $result[] = $this->arguments['keys'][$order];
                }
                return $result;
            });
        } else {
            $arguments = array_values($this->arguments['keys']);
        }
        return $closure(...$arguments);
    }
}

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
namespace Hyperf\RpcServer\Event;

use Hyperf\RpcServer\Annotation\RpcService as Annotation;
use Hyperf\Utils\Contracts\Arrayable;

class AfterPathRegister implements Arrayable
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

    public function __construct(string $path, string $className, string $methodName, Annotation $annotation)
    {
        $this->path = $path;
        $this->className = $className;
        $this->methodName = $methodName;
        $this->annotation = $annotation;
    }

    public function toArray(): array
    {
        return [
            'path' => $this->path,
            'className' => $this->className,
            'methodName' => $this->methodName,
            'annotation' => $this->annotation->toArray(),
        ];
    }
}

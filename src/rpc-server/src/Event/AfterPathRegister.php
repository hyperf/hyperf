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

use Hyperf\Contract\Arrayable;
use Hyperf\RpcServer\Annotation\RpcService as Annotation;

class AfterPathRegister implements Arrayable
{
    public function __construct(public string $path, public string $className, public string $methodName, public Annotation $annotation)
    {
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

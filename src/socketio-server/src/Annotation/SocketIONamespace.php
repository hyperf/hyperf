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

namespace Hyperf\SocketIOServer\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\SocketIOServer\Collector\SocketIORouter;

#[Attribute(Attribute::TARGET_CLASS)]
class SocketIONamespace extends AbstractAnnotation
{
    public function __construct(public string $namespace = '/')
    {
    }

    public function collectClass(string $className): void
    {
        SocketIORouter::addNamespace($this->namespace, $className);
        parent::collectClass($className);
    }
}

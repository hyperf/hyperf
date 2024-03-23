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

namespace Hyperf\Di\Aop;

use PhpParser\Node;

class VisitorMetadata
{
    public bool $hasConstructor = false;

    public ?Node\Stmt\ClassMethod $constructorNode = null;

    public ?bool $hasExtends = null;

    /**
     * The class name of \PhpParser\Node\Stmt\ClassLike.
     */
    public ?string $classLike = null;

    public function __construct(public string $className)
    {
    }
}

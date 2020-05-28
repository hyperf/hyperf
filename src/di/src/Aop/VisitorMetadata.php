<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Di\Aop;

use PhpParser\Node;

class VisitorMetadata
{
    /**
     * @var string
     */
    public $className;

    /**
     * @var bool
     */
    public $hasConstructor;

    /**
     * @var null|Node\Stmt\ClassMethod
     */
    public $constructorNode;
}

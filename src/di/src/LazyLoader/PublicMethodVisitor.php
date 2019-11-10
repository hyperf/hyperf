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

namespace Hyperf\Di\LazyLoader;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\MagicConst\Function_ as MagicConstFunction;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Return_;

class PublicMethodVisitor extends NodeVisitorAbstract
{
    /**
     * All The nodes containing public methods.
     *
     * @var Node[]
     */
    public $nodes = [];

    public function enterNode(Node $node)
    {
        if ($node instanceof ClassMethod) {
            $staticCall =
                new Return_(new MethodCall(new Variable('this'), '__call', [
                    new Node\Arg(new MagicConstFunction()),
                    new Node\Arg(new FuncCall(new Name('func_get_args'))),
                ]));
            $node->stmts = [
                $staticCall,
            ];
            $node->flags &= ~Class_::MODIFIER_ABSTRACT;
        }
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof ClassMethod && $node->isPublic()) {
            $this->nodes[] = $node;
        }
    }
}

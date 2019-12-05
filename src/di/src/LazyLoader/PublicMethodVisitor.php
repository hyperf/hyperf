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
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\MagicConst\Function_ as MagicConstFunction;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeVisitorAbstract;

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
            $methodCall =
                new MethodCall(new Variable('this'), '__call', [
                    new Node\Arg(new MagicConstFunction()),
                    new Node\Arg(new FuncCall(new Name('func_get_args'))),
                ]);
            if ($node->returnType && $node->returnType->toString() === 'void') {
                $methodCall = new Expression($methodCall);
            } else {
                $methodCall = new Return_($methodCall);
            }
            $node->stmts = [
                $methodCall,
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

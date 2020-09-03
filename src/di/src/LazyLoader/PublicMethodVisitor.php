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
use PhpParser\Node\Stmt\Interface_;
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

    /**
     * @var Node\Stmt[]
     */
    private $stmts;

    /**
     * @var string
     */
    private $originalClassName;

    public function __construct(array $stmts, string $originalClassName)
    {
        $this->stmts = $stmts;
        if (strpos($originalClassName, '\\') !== 0) {
            $originalClassName = '\\' . $originalClassName;
        }
        $this->originalClassName = $originalClassName;
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof Interface_ || $node instanceof Class_) {
            $node->stmts = $this->stmts;
        }
        if ($node instanceof ClassMethod) {
            $methodCall = new MethodCall(
                new Variable('this'),
                '__call',
                [
                    new Node\Arg(new MagicConstFunction()),
                    new Node\Arg(new FuncCall(new Name('func_get_args'))),
                ]
            );
            $shouldReturn = true;
            if ($node->getReturnType() && method_exists($node->getReturnType(), 'toString')) {
                if ($node->getReturnType()->toString() === 'self') {
                    $node->returnType = new Name($this->originalClassName);
                }
                if ($node->getReturnType()->toString() === 'void') {
                    $shouldReturn = false;
                    $methodCall = new Expression($methodCall);
                }
            }
            $shouldReturn && $methodCall = new Return_($methodCall);
            $node->stmts = [
                $methodCall,
            ];
            $node->flags &= ~Class_::MODIFIER_ABSTRACT;
        }
        return null;
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof ClassMethod && $node->isPublic() && ! $node->isMagic()) {
            $this->nodes[] = $node;
        }
        return null;
    }
}

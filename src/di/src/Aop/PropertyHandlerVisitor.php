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

use Hyperf\Di\BetterReflectionManager;
use Hyperf\Di\Inject\InjectTrait;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\NodeVisitorAbstract;

class PropertyHandlerVisitor extends NodeVisitorAbstract
{
    /**
     * @var bool
     */
    protected $hasConstructor = false;

    /**
     * @var bool
     */
    protected $hasReflectConstructor = false;

    /**
     * @var array
     */
    protected $proxyTraits
        = [
            PropertyHandlerTrait::class,
        ];

    /**
     * @var \Hyperf\Di\Aop\VisitorMetadata
     */
    protected $visitorMetadata;

    public function __construct(VisitorMetadata $visitorMetadata)
    {
        $this->visitorMetadata = $visitorMetadata;
    }

    public function setClassName(string $classname)
    {
        $this->visitorMetadata->className = $classname;
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\ClassMethod) {
            if ($node->name->toString() === '__construct') {
                $this->visitorMetadata->hasConstructor = true;
                return;
            }
        }
    }

    public function leaveNode(Node $node)
    {
        if (! $this->visitorMetadata->hasConstructor && $node instanceof Node\Stmt\Class_ && ! $node->isAnonymous()) {
            $constructor = $this->buildConstructor();
            $node->stmts = array_merge([$this->buildProxyTraitUseStatement()], [$constructor], $node->stmts);
            $this->visitorMetadata->hasConstructor = true;
        } else {
            if ($node instanceof Node\Stmt\ClassMethod && $node->name->toString() === '__construct') {
                $node->stmts = array_merge([$this->buildStaticCallStatement()], $node->stmts);
            }
            if ($node instanceof Node\Stmt\Class_ && ! $node->isAnonymous()) {
                $node->stmts = array_merge([$this->buildProxyTraitUseStatement()], $node->stmts);
            }
        }
    }

    // protected function buildCallParentConstructorStatement(): Node\Stmt
    // {
    //     $left = new Node\Expr\FuncCall(new Name('get_parent_class'), [
    //         new Node\Arg(new Node\Expr\Variable('this')),
    //     ]);
    //     $right = new Node\Expr\FuncCall(new Name('method_exists'), [
    //         new Node\Arg(new Node\Expr\ClassConstFetch(new Name('parent'), new Name('class'))),
    //         new Node\Arg(new Node\Scalar\String_('__construct')),
    //     ]);
    //     return new Node\Stmt\If_(new Node\Expr\BinaryOp\BooleanAnd($left, $right), [
    //         'stmts' => [
    //             new Node\Stmt\Expression(new Node\Expr\StaticCall(new Name('parent'), '__construct', [
    //                 new Node\Arg(new Node\Expr\FuncCall(new Name('func_get_args'), [], [
    //                     'unpack' => true,
    //                 ])),
    //             ])),
    //         ],
    //     ]);
    // }

    protected function buildConstructor(): Node\Stmt\ClassMethod
    {
        $constructor = new Node\Stmt\ClassMethod('__construct');

        $reflection = BetterReflectionManager::reflectClass($this->visitorMetadata->className);
        if ($reflection->hasMethod('__construct')) {
            $parameters = $reflection->getMethod('__construct')->getParameters();
            foreach ($parameters as $parameter) {
                $constructor->params[] = $parameter->getAst();
            }
        }

        if (count($constructor->params) > 0) {
            $constructor->stmts[] = $this->buildCallParentConstructorStatement();
        }
        $constructor->stmts[] = $this->buildStaticCallStatement();

        return $constructor;
    }

    protected function buildCallParentConstructorStatement(): ?Node\Stmt
    {
        return new Node\Stmt\Expression(new Node\Expr\StaticCall(new Name('parent'), '__construct', [
            new Node\Arg(new Node\Expr\FuncCall(new Name('func_get_args')), false, true),
        ]));
    }

    protected function buildStaticCallStatement(): Node\Stmt\Expression
    {
        return new Node\Stmt\Expression(new Node\Expr\StaticCall(new Name('self'), '__handlePropertyHandler', [
            new Node\Arg(new Node\Scalar\MagicConst\Class_()),
        ]));
    }

    /**
     * Build `use InjectTrait;` statement.
     */
    protected function buildProxyTraitUseStatement(): TraitUse
    {
        $traits = [];
        foreach ($this->proxyTraits as $proxyTrait) {
            // Should not check the trait whether or not exist to avoid class autoload.
            if (! is_string($proxyTrait)) {
                continue;
            }
            // Add backslash prefix if the proxy trait does not start with backslash.
            $proxyTrait[0] !== '\\' && $proxyTrait = '\\' . $proxyTrait;
            $traits[] = new Name($proxyTrait);
        }
        return new TraitUse($traits);
    }
}

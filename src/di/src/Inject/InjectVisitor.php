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
namespace Hyperf\Di\Inject;

use Hyperf\Di\BetterReflectionManager;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\NodeVisitorAbstract;

class InjectVisitor extends NodeVisitorAbstract
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
     * @var string
     */
    protected $classname = '';

    /**
     * @var array
     */
    protected $proxyTraits
        = [
            InjectTrait::class,
        ];

    public function setClassName(string $classname)
    {
        $this->classname = $classname;
        $reflection = BetterReflectionManager::getClassReflector()->reflect($classname);
        $this->hasReflectConstructor = $reflection->hasMethod('__construct');
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\ClassMethod) {
            if ($node->name->toString() === '__construct') {
                $this->hasConstructor = true;
                return;
            }
        }
    }

    public function leaveNode(Node $node)
    {
        if (! $this->hasConstructor && $node instanceof Node\Stmt\Class_ && ! $node->isAbstract() && ! $node->isAnonymous()) {
            if ($this->hasReflectConstructor && $this->classname) {
                $reflection = BetterReflectionManager::getClassReflector()->reflect($this->classname);
                $constructor = $reflection->getParentClass()->getMethod('__construct')->getAst();
                $constructor->stmts = [$this->buildUseParentConstructor($constructor->getParams()), $this->buildStaticCallStatement()];
            } else {
                $constructor = new Node\Stmt\ClassMethod('__construct');
                $constructor->stmts = [$this->buildStaticCallStatement()];
            }
            $node->stmts = array_merge([$this->buildProxyTraitUseStatement()], [$constructor], $node->stmts);
        } else {
            if ($node instanceof Node\Stmt\ClassMethod && $node->name->toString() === '__construct') {
                $node->stmts = array_merge([$this->buildStaticCallStatement()], $node->stmts);
            }
            if ($node instanceof Node\Stmt\Class_ && ! $node->isAnonymous()) {
                $node->stmts = array_merge([$this->buildProxyTraitUseStatement()], $node->stmts);
            }
        }
    }

    protected function buildStaticCallStatement(): Node\Stmt\Expression
    {
        return new Node\Stmt\Expression(new Node\Expr\StaticCall(new Name('self'), '__injectProperties', [
            // OriginalClass::class
            new Node\Arg(new Node\Scalar\MagicConst\Class_()),
        ]));
    }

    /**
     * @param Node\Param[] $params
     * @return Node\Stmt\Expression
     */
    protected function buildUseParentConstructor(array $params = [])
    {
        return new Node\Stmt\Expression(new Node\Expr\StaticCall(new Name('parent'), '__construct', [
            new Node\Arg(new Node\Expr\FuncCall(new Name('func_get_args')), false, true),
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

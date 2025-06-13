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

use Hyperf\CodeParser\PhpParser;
use Hyperf\Di\ReflectionManager;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\NodeVisitorAbstract;
use ReflectionException;

class PropertyHandlerVisitor extends NodeVisitorAbstract
{
    protected array $proxyTraits = [
        PropertyHandlerTrait::class,
    ];

    public function __construct(protected VisitorMetadata $visitorMetadata)
    {
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_) {
            if ($this->visitorMetadata->hasExtends === null) {
                if ($node->extends) {
                    $this->visitorMetadata->hasExtends = true;
                } else {
                    $this->visitorMetadata->hasExtends = false;
                }
            }
        }
        if ($node instanceof Node\Stmt\ClassMethod) {
            if ($node->name->toString() === '__construct') {
                $this->visitorMetadata->hasConstructor = true;
                $this->visitorMetadata->constructorNode = $node;
            }
        }
        return null;
    }

    public function leaveNode(Node $node)
    {
        if (! $this->visitorMetadata->hasConstructor && $node instanceof Node\Stmt\Class_ && ! $node->isAnonymous()) {
            $constructor = $this->buildConstructor();
            if ($this->visitorMetadata->hasExtends) {
                $constructor->stmts[] = $this->buildCallParentConstructorStatement();
            }
            $constructor->stmts[] = $this->buildMethodCallStatement();
            $node->stmts = array_merge([$this->buildProxyTraitUseStatement()], [$constructor], $node->stmts);
            $this->visitorMetadata->hasConstructor = true;
        } else {
            if ($node instanceof Node\Stmt\ClassMethod && $node->name->toString() === '__construct') {
                $node->stmts = array_merge([$this->buildMethodCallStatement()], $node->stmts);
            }
            if ($node instanceof Node\Stmt\Class_ && ! $node->isAnonymous()) {
                $node->stmts = array_merge([$this->buildProxyTraitUseStatement()], $node->stmts);
            }
        }
        return null;
    }

    protected function buildConstructor(): Node\Stmt\ClassMethod
    {
        if ($this->visitorMetadata->constructorNode instanceof Node\Stmt\ClassMethod) {
            // Returns the parsed constructor class method node.
            $constructor = $this->visitorMetadata->constructorNode;
        } else {
            // Create a new constructor class method node.
            $constructor = new Node\Stmt\ClassMethod('__construct');
            $reflection = ReflectionManager::reflectClass($this->visitorMetadata->className);
            try {
                $parameters = $reflection->getMethod('__construct')->getParameters();
                foreach ($parameters as $parameter) {
                    $constructor->params[] = PhpParser::getInstance()->getNodeFromReflectionParameter($parameter);
                }
            } catch (ReflectionException) {
                // Cannot found __construct method in parent class or traits, do nothing.
            }
        }
        return $constructor;
    }

    protected function buildCallParentConstructorStatement(): Node\Stmt
    {
        $hasConstructor = new Node\Expr\FuncCall(new Name('method_exists'), [
            new Node\Arg(new Node\Expr\ClassConstFetch(new Name('parent'), 'class')),
            new Node\Arg(new Node\Scalar\String_('__construct')),
        ]);
        return new Node\Stmt\If_($hasConstructor, [
            'stmts' => [
                new Node\Stmt\Expression(new Node\Expr\StaticCall(new Name('parent'), '__construct', [
                    new Node\Arg(new Node\Expr\FuncCall(new Name('func_get_args')), false, true),
                ])),
            ],
        ]);
    }

    protected function buildMethodCallStatement(): Node\Stmt\Expression
    {
        return new Node\Stmt\Expression(new Node\Expr\MethodCall(new Node\Expr\Variable('this'), '__handlePropertyHandler', [
            new Node\Arg(new Node\Scalar\MagicConst\Class_()),
        ]));
    }

    /**
     * Build `use PropertyHandlerTrait;` statement.
     */
    protected function buildProxyTraitUseStatement(): TraitUse
    {
        $traits = [];
        foreach ($this->proxyTraits as $proxyTrait) {
            // Should not check the trait whether exist to avoid class autoload.
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

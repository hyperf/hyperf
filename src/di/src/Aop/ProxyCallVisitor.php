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
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\MagicConst\Function_ as MagicConstFunction;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeVisitorAbstract;

class ProxyCallVisitor extends NodeVisitorAbstract
{
    /**
     * Determine if the class used proxy trait.
     *
     * @var bool
     */
    private $useProxyTrait = false;

    /**
     * Define the proxy handler trait here.
     *
     * @var array
     */
    private $proxyTraits
        = [
            ProxyTrait::class,
        ];

    /**
     * @var null|Identifier
     */
    private $class;

    /**
     * @var null|Name
     */
    private $extends;

    /**
     * @var string
     */
    private $classname;

    public function __construct(string $classname)
    {
        $this->classname = $classname;
    }

    public function beforeTraverse(array $nodes)
    {
        foreach ($nodes as $namespace) {
            if ($namespace instanceof Node\Stmt\Declare_) {
                continue;
            }
            if (! $namespace instanceof Namespace_) {
                break;
            }
            // Add current class namespace.
            $usedNamespace = [
                $namespace->name->toCodeString(),
            ];
            foreach ($namespace->stmts as $class) {
                switch ($class) {
                    case $class instanceof Use_:
                        // Collect the namespace which the current class imported.
                        foreach ($class->uses as $classUse) {
                            $usedNamespace[] = $classUse->name->toCodeString();
                        }
                        break;
                    case $class instanceof Class_ && ! $class->isAnonymous():
                        $this->class = $class->name;
                        if ($class->extends) {
                            $this->extends = $class->extends;
                        }
                        // Determine if the current class has used the proxy trait.
                        foreach ($class->stmts as $subNode) {
                            if ($subNode instanceof TraitUse) {
                                /** @var Name $trait */
                                foreach ($subNode->traits as $trait) {
                                    if ($this->isMatchUseTrait($usedNamespace, $trait->toCodeString())) {
                                        $this->useProxyTrait = true;
                                    }
                                }
                            }
                        }
                        break;
                }
            }
        }

        return null;
    }

    public function leaveNode(Node $node)
    {
        switch ($node) {
            case $node instanceof ClassMethod:
                if (! $this->shouldRewrite($node)) {
                    return $this->formatMethod($node);
                }
                // Rewrite the method to proxy call method.
                return $this->rewriteMethod($node);
                break;
            case $node instanceof Class_ && ! $node->isAnonymous():
                // Add use proxy traits.
                $stmts = $node->stmts;
                array_unshift($stmts, $this->buildProxyCallTraitUseStatement());
                $node->stmts = $stmts;
                unset($stmts);
                return $node;
                break;
            case $node instanceof StaticPropertyFetch && $this->extends:
                // Rewrite parent::$staticProperty to ParentClass::$staticProperty.
                if ($node->class instanceof Node\Name && $node->class->toString() === 'parent') {
                    $node->class = new Name($this->extends->toCodeString());
                    return $node;
                }
                break;
            case $node instanceof Node\Scalar\MagicConst\Function_:
                // Rewrite __FUNCTION__ to $__function__ variable.
                return new Variable('__function__');
                break;
            case $node instanceof Node\Scalar\MagicConst\Method:
                // Rewrite __METHOD__ to $__method__ variable.
                return new Variable('__method__');
                break;
        }
    }

    /**
     * @param array $namespaces the namespaces that the current class imported
     * @param string $trait the full namespace of trait or the trait name
     */
    private function isMatchUseTrait(array $namespaces, string $trait): bool
    {
        // @TODO use $this->proxyTraits.
        $proxyTrait = ProxyTrait::class;
        $trait = ltrim($trait, '\\');
        if ($trait === $proxyTrait) {
            return true;
        }
        foreach ($namespaces as $namespace) {
            if (ltrim($namespace, '\\') . '\\' . $trait === $proxyTrait) {
                return true;
            }
        }
        return false;
    }

    /**
     * Build `use ProxyTrait;`.
     */
    private function buildProxyCallTraitUseStatement(): TraitUse
    {
        $traits = [];
        foreach ($this->proxyTraits as $proxyTrait) {
            if (! is_string($proxyTrait) || ! trait_exists($proxyTrait)) {
                continue;
            }
            // Add backslash prefix if the proxy trait does not start with backslash.
            $proxyTrait[0] !== '\\' && $proxyTrait = '\\' . $proxyTrait;
            $traits[] = new Name($proxyTrait);
        }
        return new TraitUse($traits);
    }

    /**
     * Format a normal class method of no need proxy call.
     */
    private function formatMethod(ClassMethod $node)
    {
        if ($node->name->toString() === '__construct') {
            // Rewrite parent::__construct to class::__construct.
            foreach ($node->stmts as $stmt) {
                if ($stmt instanceof Expression && $stmt->expr instanceof Node\Expr\StaticCall) {
                    $class = $stmt->expr->class;
                    if ($class instanceof Node\Name && $class->toString() === 'parent') {
                        $stmt->expr->class = new Node\Name($this->extends->toCodeString());
                    }
                }
            }
        }

        return $node;
    }

    /**
     * Rewrite a normal class method to a proxy call method,
     * include normal class method and static method.
     */
    private function rewriteMethod(ClassMethod $node): ClassMethod
    {
        // Build the static proxy call method base on the original method.
        if (! $this->class) {
            return $node;
        }
        $shouldReturn = true;
        $returnType = $node->getReturnType();
        if ($returnType instanceof Identifier && $returnType->name === 'void') {
            $shouldReturn = false;
        }
        $class = $this->class->toString();
        $staticCall = new StaticCall(new Name('self'), '__proxyCall', [
            // OriginalClass::class
            new Node\Arg(new ClassConstFetch(new Name($class), new Identifier('class'))),
            // __FUNCTION__
            new Node\Arg(new MagicConstFunction()),
            // self::getParamMap(OriginalClass::class, __FUNCTION, func_get_args())
            new Node\Arg(new StaticCall(new Name('self'), 'getParamsMap', [
                new Node\Arg(new ClassConstFetch(new Name($class), new Identifier('class'))),
                new Node\Arg(new MagicConstFunction()),
                new Node\Arg(new FuncCall(new Name('func_get_args'))),
            ])),
            // A closure that wrapped original method code.
            new Node\Arg(new Closure([
                'params' => value(function () use ($node) {
                    // Transfer the variadic variable to normal variable at closure argument. ...$params => $parms
                    $params = $node->getParams();
                    foreach ($params as $key => $param) {
                        if ($param instanceof Node\Param && $param->variadic) {
                            $newParam = clone $param;
                            $newParam->variadic = false;
                            $params[$key] = $newParam;
                        }
                    }
                    return $params;
                }),
                'uses' => [
                    new Variable('__function__'),
                    new Variable('__method__'),
                ],
                'stmts' => $node->stmts,
            ])),
        ]);
        $magicConstFunction = new Expression(new Assign(new Variable('__function__'), new Node\Scalar\MagicConst\Function_()));
        $magicConstMethod = new Expression(new Assign(new Variable('__method__'), new Node\Scalar\MagicConst\Method()));
        if ($shouldReturn) {
            $node->stmts = [
                $magicConstFunction,
                $magicConstMethod,
                new Return_($staticCall),
            ];
        } else {
            $node->stmts = [
                $magicConstFunction,
                $magicConstMethod,
                new Expression($staticCall),
            ];
        }
        return $node;
    }

    private function shouldRewrite(ClassMethod $node)
    {
        if (! $node->name) {
            return false;
        }

        $rewriteCollection = Aspect::parse($this->classname);

        return $rewriteCollection->shouldRewrite($node->name->toString());
    }
}

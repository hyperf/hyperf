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
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\MagicConst\Class_ as MagicConstClass;
use PhpParser\Node\Scalar\MagicConst\Function_ as MagicConstFunction;
use PhpParser\Node\Scalar\MagicConst\Method as MagicConstMethod;
use PhpParser\Node\Scalar\MagicConst\Trait_ as MagicConstTrait;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\NodeVisitorAbstract;

class ProxyCallVisitor extends NodeVisitorAbstract
{
    /**
     * @var \Hyperf\Di\Aop\VisitorMetadata
     */
    protected $visitorMetadata;

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
     * @var bool
     */
    private $shouldRewrite = false;

    public function __construct(VisitorMetadata $visitorMetadata)
    {
        $this->visitorMetadata = $visitorMetadata;
    }

    public function beforeTraverse(array $nodes)
    {
        foreach ($nodes as $namespace) {
            if ($namespace instanceof Node\Stmt\Declare_) {
                continue;
            }

            if (! $namespace instanceof Node\Stmt\Namespace_) {
                break;
            }

            foreach ($namespace->stmts as $class) {
                switch ($class) {
                    case $class instanceof Node\Stmt\ClassLike:
                        $this->visitorMetadata->classLike = get_class($class);
                        break;
                }
            }
        }

        return null;
    }

    public function enterNode(Node $node)
    {
        switch ($node) {
            case $node instanceof ClassMethod:
                if ($this->shouldRewrite($node)) {
                    $this->shouldRewrite = true;
                } else {
                    $this->shouldRewrite = false;
                }
                break;
        }

        return null;
    }

    public function leaveNode(Node $node)
    {
        switch ($node) {
            case $node instanceof ClassMethod:
                if (! $this->shouldRewrite($node)) {
                    return $node;
                }
                // Rewrite the method to proxy call method.
                return $this->rewriteMethod($node);
            case $node instanceof Node\Stmt\Trait_:
                // If the node is trait and php version >= 7.3, it can `use ProxyTrait` like class.
            case $node instanceof Class_ && ! $node->isAnonymous():
                // Add use proxy traits.
                $stmts = $node->stmts;
                if ($stmt = $this->buildProxyCallTraitUseStatement()) {
                    array_unshift($stmts, $stmt);
                }
                $node->stmts = $stmts;
                unset($stmts);
                return $node;
            case $node instanceof MagicConstFunction:
                // Rewrite __FUNCTION__ to $__function__ variable.
                if ($this->shouldRewrite) {
                    return new Variable('__function__');
                }
                break;
            case $node instanceof MagicConstMethod:
                // Rewrite __METHOD__ to $__method__ variable.
                if ($this->shouldRewrite) {
                    return new Variable('__method__');
                }
                break;
        }
        return null;
    }

    /**
     * Build `use ProxyTrait;`.
     */
    private function buildProxyCallTraitUseStatement(): ?TraitUse
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

        if (empty($traits)) {
            return null;
        }
        return new TraitUse($traits);
    }

    /**
     * Rewrite a normal class method to a proxy call method,
     * include normal class method and static method.
     */
    private function rewriteMethod(ClassMethod $node): ClassMethod
    {
        // Build the static proxy call method base on the original method.
        $shouldReturn = true;
        $returnType = $node->getReturnType();
        if ($returnType instanceof Identifier && $returnType->name === 'void') {
            $shouldReturn = false;
        }
        $staticCall = new StaticCall(new Name('self'), '__proxyCall', [
            // __CLASS__
            new Arg($this->getMagicConst()),
            // __FUNCTION__
            new Arg(new MagicConstFunction()),
            // self::getParamMap(OriginalClass::class, __FUNCTION, func_get_args())
            new Arg(new StaticCall(new Name('self'), '__getParamsMap', [
                new Arg(new MagicConstClass()),
                new Arg(new MagicConstFunction()),
                new Arg(new FuncCall(new Name('func_get_args'))),
            ])),
            // A closure that wrapped original method code.
            new Arg(new Closure([
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
        $stmts = $this->unshiftMagicMethods([]);
        if ($shouldReturn) {
            $stmts[] = new Return_($staticCall);
        } else {
            $stmts[] = new Expression($staticCall);
        }
        $node->stmts = $stmts;
        return $node;
    }

    private function unshiftMagicMethods($stmts = [])
    {
        $magicConstFunction = new Expression(new Assign(new Variable('__function__'), new MagicConstFunction()));
        $magicConstMethod = new Expression(new Assign(new Variable('__method__'), new MagicConstMethod()));
        array_unshift($stmts, $magicConstFunction, $magicConstMethod);
        return $stmts;
    }

    private function getMagicConst(): Node\Scalar\MagicConst
    {
        switch ($this->visitorMetadata->classLike) {
            case Trait_::class:
                return new MagicConstTrait();
            case Class_::class:
            default:
                return new MagicConstClass();
        }
    }

    private function shouldRewrite(ClassMethod $node)
    {
        if (in_array($this->visitorMetadata->classLike, [Node\Stmt\Interface_::class])) {
            return false;
        }

        $rewriteCollection = Aspect::parse($this->visitorMetadata->className);

        return $rewriteCollection->shouldRewrite($node->name->toString());
    }
}

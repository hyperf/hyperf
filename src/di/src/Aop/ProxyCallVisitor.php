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

use Hyperf\Support\Composer;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\MagicConst\Class_ as MagicConstClass;
use PhpParser\Node\Scalar\MagicConst\Dir as MagicConstDir;
use PhpParser\Node\Scalar\MagicConst\File as MagicConstFile;
use PhpParser\Node\Scalar\MagicConst\Function_ as MagicConstFunction;
use PhpParser\Node\Scalar\MagicConst\Method as MagicConstMethod;
use PhpParser\Node\Scalar\MagicConst\Trait_ as MagicConstTrait;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\NodeVisitorAbstract;

class ProxyCallVisitor extends NodeVisitorAbstract
{
    /**
     * Define the proxy handler trait here.
     */
    private array $proxyTraits = [
        ProxyTrait::class,
    ];

    private bool $shouldRewrite = false;

    public function __construct(protected VisitorMetadata $visitorMetadata)
    {
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
                if ($class instanceof Node\Stmt\ClassLike) {
                    $this->visitorMetadata->classLike = get_class($class);
                }
            }
        }

        return null;
    }

    public function enterNode(Node $node)
    {
        switch ($node) {
            case $node instanceof ClassMethod:
                $this->shouldRewrite = $this->shouldRewrite($node);
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
            case $node instanceof Trait_:
                // If the node is trait and php version >= 7.3, it can `use ProxyTrait` like class.
            case $node instanceof Enum_:
                // If the node is enum and php version >= 8.1, it can `use ProxyTrait` like class.
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
            case $node instanceof MagicConstDir:
                // Rewrite __DIR__ as the real directory path
                if ($file = Composer::getLoader()->findFile($this->visitorMetadata->className)) {
                    return new String_(dirname(realpath($file)));
                }
                break;
            case $node instanceof MagicConstFile:
                // Rewrite __FILE__ to the real file path
                if ($file = Composer::getLoader()->findFile($this->visitorMetadata->className)) {
                    return new String_(realpath($file));
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
            // ['order' => ['param1', 'param2'], 'keys' => compact('param1', 'param2'), 'variadic' => 'param2']
            new Arg($this->getArguments($node->getParams())),
            // A closure that wrapped original method code.
            new Arg(new Closure([
                'params' => $this->filterModifier($node->getParams()),
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

    /**
     * @param Node\Param[] $params
     * @return Node\Param[]
     */
    private function filterModifier(array $params): array
    {
        return array_map(function (Node\Param $param) {
            $tempParam = clone $param;
            $tempParam->flags &= ~Class_::VISIBILITY_MODIFIER_MASK & ~Class_::MODIFIER_READONLY;
            return $tempParam;
        }, $params);
    }

    /**
     * @param Node\Param[] $params
     */
    private function getArguments(array $params): Array_
    {
        if (empty($params)) {
            return new Array_([
                new ArrayItem(
                    key: new String_('keys'),
                    value: new Array_([], ['kind' => Array_::KIND_SHORT]),
                ),
            ], ['kind' => Array_::KIND_SHORT]);
        }
        // ['param1', 'param2', ...]
        $methodParamsList = new Array_(
            array_map(fn (Node\Param $param) => new ArrayItem(new String_($param->var->name)), $params),
            ['kind' => Array_::KIND_SHORT]
        );
        return new Array_([
            new ArrayItem(
                key: new String_('order'),
                value: $methodParamsList,
            ),
            new ArrayItem(
                key: new String_('keys'),
                value: new FuncCall(new Name('compact'), [new Arg($methodParamsList)])
            ),
            new ArrayItem(
                key: new String_('variadic'),
                value: $this->getVariadicParamName($params),
            )], ['kind' => Array_::KIND_SHORT]);
    }

    /**
     * @param Node\Param[] $params
     */
    private function getVariadicParamName(array $params): String_
    {
        foreach ($params as $param) {
            if ($param->variadic) {
                return new String_($param->var->name);
            }
        }
        return new String_('');
    }

    private function unshiftMagicMethods(array $stmts = []): array
    {
        $magicConstFunction = new Expression(new Assign(new Variable('__function__'), new MagicConstFunction()));
        $magicConstMethod = new Expression(new Assign(new Variable('__method__'), new MagicConstMethod()));
        array_unshift($stmts, $magicConstFunction, $magicConstMethod);
        return $stmts;
    }

    private function getMagicConst(): Node\Scalar\MagicConst
    {
        return match ($this->visitorMetadata->classLike) {
            Trait_::class => new MagicConstTrait(),
            default => new MagicConstClass(),
        };
    }

    private function shouldRewrite(ClassMethod $node): bool
    {
        if ($this->visitorMetadata->classLike == Node\Stmt\Interface_::class || $node->isAbstract()) {
            return false;
        }

        $rewriteCollection = Aspect::parse($this->visitorMetadata->className);

        return $rewriteCollection->shouldRewrite($node->name->toString());
    }
}

<?php
declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Di\Aop;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\MagicConst\Function_ as MagicConstFunction;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeVisitorAbstract;

class ProxyCallVistor extends NodeVisitorAbstract
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
     * @var Identifier
     */
    private $class;

    /**
     * @var Name|null
     */
    private $extends;

    public function beforeTraverse(array $nodes)
    {
        foreach ($nodes as $namespace) {
            if (! $namespace instanceof Namespace_) {
                return;
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
                    case $class instanceof Class_:
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
    }

    public function leaveNode(Node $node)
    {
        switch ($node) {
            case $node instanceof ClassMethod:
                if ($node->name && $node->name->toString() === '__construct') {
                    return $node;
                }
                // Rewrite the method to proxy call method.
                return $this->rewriteMethod($node);
                break;
            case $node instanceof Class_:
                // Add use proxy traits.
                $stmts = $node->stmts;
                array_unshift($stmts, $this->buildProxyCallTraitUseStatement());
                $node->stmts = $stmts;
                unset($stmts);
                return $node;
                break;
            case $node instanceof StaticPropertyFetch && $this->extends:
                // Rewrite parent::$staticProperty to ParentClass::$staticProperty.
                if ($node->class && $node->class->toString() === 'parent') {
                    $node->class = new Name($this->extends->toCodeString());
                    return $node;
                }
                break;
        }
    }

    /**
     * @param array $namespaces The namespaces that the current class imported.
     * @param string $trait The full namespace of trait or the trait name.
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
     * Build `use ProxyTrait;`
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
     * Rewrite a normal class method to a proxy call method,
     * include normal class method and static method.
     */
    private function rewriteMethod(ClassMethod $node): ClassMethod
    {
        // Build the static proxy call method base on the original method.
        if (! $this->class) {
            return $node;
        }
        $class = $this->class->toString();
        $node->stmts = [
            new Return_(new StaticCall(new Name('self'), '__proxyCall', [
                // OriginalClass::class
                new ClassConstFetch(new Name($class), new Identifier('class')),
                // __FUNCTION__
                new MagicConstFunction(),
                // self::getParamMap(OriginalClass::class, __FUNCTION, func_get_args())
                new StaticCall(new Name('self'), 'getParamsMap', [
                    new ClassConstFetch(new Name($class), new Identifier('class')),
                    new MagicConstFunction(),
                    new FuncCall(new Name('func_get_args'))
                ]),
                // A closure that wrapped original method code.
                new Closure([
                    'uses' => $this->getParametersWithoutTypehint($node),
                    'stmts' => $node->stmts,
                ]),
            ]))
        ];
        return $node;
    }

    /**
     * Get the parameters of method, without parameter typehint.
     */
    private function getParametersWithoutTypehint(ClassMethod $node): array
    {
        $parametersWithoutTypehint = value(function () use ($node) {
            // Remove the parameter typehint, otherwise will cause syntax error.
            $params = [];
            foreach ($node->getParams() as $param) {
                /** @var \PhpParser\Node\Param $param */
                // Should create a new param node, modify the original node will change the original status.
                $newParam = clone $param;
                $newParam->type = null;
                $newParam->default = null;
                $params[] = $newParam;
            }
            return $params;
        });
        return $parametersWithoutTypehint;
    }
}

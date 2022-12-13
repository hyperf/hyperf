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
namespace Hyperf\RpcClient\Proxy;

use Hyperf\Utils\CodeGen\PhpParser;
use InvalidArgumentException;
use PhpParser\Node;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\NodeVisitorAbstract;
use ReflectionMethod;

class ProxyCallVisitor extends NodeVisitorAbstract
{
    protected array $nodes = [];

    public function __construct(private string $classname, private string $originClassname)
    {
    }

    public function beforeTraverse(array $nodes)
    {
        $this->nodes = $nodes;

        return null;
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Interface_) {
            $node->stmts = $this->generateStmts();
            return new Node\Stmt\Class_($this->classname, [
                'stmts' => $node->stmts,
                'extends' => new Node\Name\FullyQualified(AbstractProxyService::class),
                'implements' => [
                    $node->name,
                ],
            ]);
        }
        return null;
    }

    public function generateStmts(): array
    {
        $methods = PhpParser::getInstance()->getAllMethodsFromStmts($this->nodes);
        $stmts = [];
        foreach ($methods as $method) {
            $stmts[] = $this->overrideMethod($method);
        }

        $parentMethods = $this->getParentMethods($this->originClassname);
        foreach ($parentMethods as $method) {
            $stmts[] = $this->overrideParentMethod($method);
        }

        return $stmts;
    }

    protected function overrideMethod(Node\FunctionLike $stmt): Node\Stmt\ClassMethod
    {
        if (! $stmt instanceof Node\Stmt\ClassMethod) {
            throw new InvalidArgumentException('stmt must instanceof Node\Stmt\ClassMethod');
        }
        $stmt->stmts = value(function () use ($stmt) {
            $methodCall = new Node\Expr\MethodCall(
                new Node\Expr\PropertyFetch(new Node\Expr\Variable('this'), new Node\Identifier('client')),
                new Node\Identifier('__call'),
                [
                    new Node\Arg(new Node\Scalar\MagicConst\Function_()),
                    new Node\Arg(new Node\Expr\FuncCall(new Node\Name('func_get_args'))),
                ]
            );
            if ($this->shouldReturn($stmt)) {
                return [new Node\Stmt\Return_($methodCall)];
            }
            return [new Node\Stmt\Expression($methodCall)];
        });

        return $stmt;
    }

    protected function overrideParentMethod(\ReflectionMethod $method)
    {
        $methodCall = new Node\Expr\MethodCall(
            new Node\Expr\PropertyFetch(new Node\Expr\Variable('this'), new Node\Identifier('client')),
            new Node\Identifier('__call'),
            [
                new Node\Arg(new Node\Scalar\MagicConst\Function_()),
                new Node\Arg(new Node\Expr\FuncCall(new Node\Name('func_get_args'))),
            ]
        );

        $hasReturn = true;
        $methodReturnType = $method->getReturnType();
        if ($method->hasReturnType()
            && ($methodReturnType || $methodReturnType?->getName() === 'void')
        ) {
            $hasReturn = false;
        }

        if ($hasReturn) {
            $stmt = [new Node\Stmt\Return_($methodCall)];
        } else {
            $stmt = [new Node\Stmt\Expression($methodCall)];
        }

        return new Node\Stmt\ClassMethod(
            $method->getName(),
            [
                'flags' => ReflectionMethod::IS_PUBLIC,
                'params' => $method->getParameters(),
                'stmts' => $stmt,
            ]
        );
    }

    protected function shouldReturn(Node\Stmt\ClassMethod $stmt): bool
    {
        return $stmt->getReturnType() instanceof Node\NullableType
            || $stmt->getReturnType() instanceof Node\UnionType
            || ((string) $stmt->getReturnType()) !== 'void';
    }

    protected function getParentMethods(string $className): array
    {
        $parentMethods = [];
        $currentClass = new \ReflectionClass($className);

        $parentInterface = $currentClass->getInterfaces();
        foreach ($parentInterface as $interface) {
            foreach ($interface->getMethods() as $method) {
                ! isset($parentMethods[$method->getName()]) && $parentMethods[$method->getName()] = $method;
            }
        }

        return $parentMethods;
    }

}

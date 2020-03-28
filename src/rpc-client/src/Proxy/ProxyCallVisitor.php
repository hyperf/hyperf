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

namespace Hyperf\RpcClient\Proxy;

use Hyperf\Di\ReflectionManager;
use PhpParser\Node;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\NodeVisitorAbstract;
use ReflectionMethod;
use ReflectionParameter;

class ProxyCallVisitor extends NodeVisitorAbstract
{
    /**
     * @var \PhpParser\Parser
     */
    protected $astParser;

    /**
     * @var CodeLoader
     */
    protected $codeLoader;

    /**
     * @var string
     */
    private $classname;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var array
     */
    private $constants = [];

    public function __construct(string $classname, CodeLoader $codeLoader, \PhpParser\Parser $astParser)
    {
        $this->classname = $classname;
        $this->codeLoader = $codeLoader;
        $this->astParser = $astParser;
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Namespace_) {
            $this->namespace = $node->name->toCodeString();
        }
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Interface_) {
            $node->stmts = $this->generateStmts($node);
            return new Node\Stmt\Class_($this->classname, [
                'stmts' => $node->stmts,
                'extends' => new Node\Name\FullyQualified(AbstractProxyService::class),
                'implements' => [
                    $node->name,
                ],
            ]);
        }
        return parent::leaveNode($node);
    }

    public function generateStmts(Interface_ $node): array
    {
        $reflectionInterface = ReflectionManager::reflectClass($this->namespace . '\\' . $node->name);
        $reflectionMethods = $reflectionInterface->getMethods(ReflectionMethod::IS_PUBLIC);
        $stmts = [];
        foreach ($reflectionMethods as $method) {
            $stmts[] = $this->handleMethodStmt($method);
        }
        return $stmts;
    }

    protected function handleMethodStmt(ReflectionMethod $method): Node\Stmt\ClassMethod
    {
        return new Node\Stmt\ClassMethod($method->getName(), [
            'flags' => 1,
            'stmts' => [new Node\Stmt\Return_(new Node\Expr\MethodCall(
                new Node\Expr\PropertyFetch(new Node\Expr\Variable('this'), new Node\Identifier('client')),
                new Node\Identifier('__call'),
                [
                    new Node\Scalar\MagicConst\Function_(),
                    new Node\Expr\FuncCall(new Node\Name('func_get_args')),
                ]
            ))],
            'params' => value(function () use ($method) {
                $parameters = [];
                foreach ($method->getParameters() as $parameter) {
                    $default = $this->handleDefaultValue($parameter);
                    $parameters[] = new Node\Param(
                        new Node\Expr\Variable($parameter->getName()),
                        $default,
                        value(function () use ($parameter) {
                            if ($parameter->isCallable()) {
                                return new Node\Identifier('callable');
                            }
                            return (string) $parameter->getType();
                        }),
                        $parameter->isPassedByReference(),
                        $parameter->isVariadic()
                    );
                }
                return $parameters;
            }),
            'returnType' => new Node\Identifier((string) $method->getReturnType()),
        ]);
    }

    protected function handleDefaultValue(ReflectionParameter $parameter): ?Node\Expr
    {
        if (! $parameter->isDefaultValueAvailable()) {
            return null;
        }
        if ($parameter->getDefaultValueConstantName()) {
            [$class, $name] = explode('::', $parameter->getDefaultValueConstantName());
            return new Node\Expr\ClassConstFetch(new Node\Name($class), $name);
        }
        return $this->transferParamValueToExpr($parameter->getDefaultValue());
    }

    protected function transferParamValueToExpr($value): Node\Expr
    {
        $type = gettype($value);
        switch ($type) {
            case 'boolean':
            case 'bool':
                return new Node\Expr\ConstFetch(new Node\Name($value ? 'true' : 'false'));
                break;
            case 'integer':
            case 'int':
                return new Node\Scalar\LNumber($value);
                break;
            case 'float':
            case 'double':
                return new Node\Scalar\DNumber($value);
                break;
            case 'string':
                return new Node\Scalar\String_($value);
                break;
            case 'array':
                return new Node\Expr\Array_(value(function () use ($value) {
                    $items = [];
                    foreach ($value as $k => $v) {
                        $items[] = new Node\Expr\ArrayItem(
                            $this->transferParamValueToExpr($v),
                            $this->transferParamValueToExpr($k)
                        );
                    }
                    return $items;
                }));
                break;
            case 'object':
                // There is no object type of default value
                break;
            case 'NULL':
                return new Node\Expr\ConstFetch(new Node\Name('null'));
                break;
        }
    }
}

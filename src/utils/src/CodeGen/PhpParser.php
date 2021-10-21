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
namespace Hyperf\Utils\CodeGen;

use Hyperf\Utils\Exception\InvalidArgumentException;
use PhpParser\Node;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use ReflectionClass;
use ReflectionParameter;

class PhpParser
{
    public const TYPES = [
        'int',
        'float',
        'string',
        'bool',
        'array',
        'object',
        'resource',
        'mixed',
    ];

    /**
     * @var null|PhpParser
     */
    protected static $instance;

    /**
     * @var Parser
     */
    protected $parser;

    public function __construct()
    {
        $parserFactory = new ParserFactory();
        $this->parser = $parserFactory->create(ParserFactory::ONLY_PHP7);
    }

    public static function getInstance(): PhpParser
    {
        if (static::$instance) {
            return static::$instance;
        }
        return static::$instance = new static();
    }

    /**
     * @return null|Node\Stmt[]
     */
    public function getNodesFromReflectionClass(ReflectionClass $reflectionClass): ?array
    {
        $code = file_get_contents($reflectionClass->getFileName());
        return $this->parser->parse($code);
    }

    public function getNodeFromReflectionParameter(ReflectionParameter $parameter): Node\Param
    {
        $result = new Node\Param(
            new Node\Expr\Variable($parameter->getName())
        );

        if ($parameter->isDefaultValueAvailable()) {
            $result->default = $this->getExprFromValue($parameter->getDefaultValue());
        }

        if ($parameter->hasType()) {
            /** @var \ReflectionNamedType|\ReflectionUnionType $reflection */
            $reflection = $parameter->getType();
            if ($reflection instanceof \ReflectionUnionType) {
                $unionType = [];
                foreach ($reflection->getTypes() as $objType) {
                    $type = $objType->getName();
                    if (! in_array($type, static::TYPES)) {
                        $unionType[] = new Node\Name('\\' . $type);
                    } else {
                        $unionType[] = new Node\Identifier($type);
                    }
                }
                $result->type = new Node\UnionType($unionType);
            } else {
                $type = $reflection->getName();
                if (! in_array($type, static::TYPES)) {
                    $result->type = new Node\Name('\\' . $type);
                } else {
                    $result->type = new Node\Identifier($type);
                }
            }
        }

        if ($parameter->isPassedByReference()) {
            $result->byRef = true;
        }

        if ($parameter->isVariadic()) {
            $result->variadic = true;
        }

        return $result;
    }

    public function getExprFromValue($value): Node\Expr
    {
        switch (gettype($value)) {
            case 'array':
                return new Node\Expr\Array_($value);
            case 'string':
                return new Node\Scalar\String_($value);
            case 'integer':
                return new Node\Scalar\LNumber($value);
            case 'double':
                return new Node\Scalar\DNumber($value);
            case 'NULL':
                return new Node\Expr\ConstFetch(new Node\Name('null'));
            case 'boolean':
                return new Node\Expr\ConstFetch(new Node\Name($value ? 'true' : 'false'));
            default:
                throw new InvalidArgumentException($value . ' is invalid');
        }
    }

    /**
     * @return Node\Stmt\ClassMethod[]
     */
    public function getAllMethodsFromStmts(array $stmts): array
    {
        $methods = [];
        foreach ($stmts as $namespace) {
            if (! $namespace instanceof Node\Stmt\Namespace_) {
                continue;
            }

            foreach ($namespace->stmts as $class) {
                if (! $class instanceof Node\Stmt\Class_ && ! $class instanceof Node\Stmt\Interface_) {
                    continue;
                }

                foreach ($class->getMethods() as $method) {
                    $methods[] = $method;
                }
            }
        }

        return $methods;
    }
}

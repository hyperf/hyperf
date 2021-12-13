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
namespace Hyperf\IDEHelper\Visitor;

use Hyperf\IDEHelper\Metadata;
use Hyperf\Utils\CodeGen\PhpParser;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use ReflectionClass;

class AnnotationIDEVisitor extends NodeVisitorAbstract
{
    protected ReflectionClass $reflection;

    protected PhpParser $parser;

    public function __construct(public Metadata $metadata)
    {
        $this->reflection = $this->metadata->reflection;
        $this->parser = PhpParser::getInstance();
    }

    public function afterTraverse(array $nodes)
    {
        foreach ($nodes as $node) {
            if ($node instanceof Node\Stmt\Declare_) {
                continue;
            }
            if ($node instanceof Node\Stmt\Namespace_) {
                foreach ($node->stmts as $class) {
                    if (! $class instanceof Node\Stmt\Class_) {
                        continue;
                    }

                    $properties = [];
                    foreach ($this->reflection->getProperties() as $property) {
                        $properties[] = new Node\Param(
                            new Node\Expr\Variable($property->getName()),
                            $this->parser->getExprFromValue($property->getDefaultValue()),
                            $this->getType($property->getType()),
                        );
                    }
                    $class->stmts = [
                        new Node\Stmt\ClassMethod('__construct', [
                            'flags' => Node\Stmt\Class_::MODIFIER_PUBLIC,
                            'params' => $properties,
                        ]),
                    ];
                }
            }
        }
    }

    private function getType(?\ReflectionType $type): Node\NullableType|Node\Identifier|null|Node\UnionType
    {
        if ($type === null) {
            return null;
        }
        if ($type instanceof \ReflectionNamedType) {
            if ($type->allowsNull()) {
                return new Node\NullableType($type->getName());
            }
            return new Node\Identifier($type->getName());
        }
        if ($type instanceof \ReflectionUnionType) {
            $result = [];
            foreach ($type->getTypes() as $type) {
                $result[] = new Node\Identifier($type->getName());
            }
            return new Node\UnionType($result);
        }
    }
}

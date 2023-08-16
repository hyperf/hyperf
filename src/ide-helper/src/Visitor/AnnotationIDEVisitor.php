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

use Hyperf\CodeParser\PhpParser;
use Hyperf\IDEHelper\Metadata;
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
                        if ($property->class !== $this->reflection->getName()) {
                            continue;
                        }
                        $properties[] = new Node\Param(
                            new Node\Expr\Variable($property->getName()),
                            $this->parser->getExprFromValue($property->getDefaultValue()),
                            $property->hasType() ? $this->parser->getNodeFromReflectionType($property->getType()) : null,
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
}

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

namespace Hyperf\Database\Commands\Ast;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use ReflectionClass;

class ModelRewriteConnectionVisitor extends NodeVisitorAbstract
{
    protected bool $hasConnection = false;

    public function __construct(protected string $class, protected string $connection)
    {
    }

    public function leaveNode(Node $node)
    {
        switch ($node) {
            case $node instanceof Node\Stmt\Property:
                if ($node->props[0]->name->toLowerString() === 'connection') {
                    $this->hasConnection = true;

                    if ($this->shouldRemovedConnection()) {
                        return NodeTraverser::REMOVE_NODE;
                    }

                    $node->props[0]->default = new Node\Scalar\String_($this->connection);
                    $node->type = new Node\NullableType(new Identifier('string'));
                }

                return $node;
        }

        return null;
    }

    public function afterTraverse(array $nodes)
    {
        if ($this->hasConnection || $this->shouldRemovedConnection()) {
            return null;
        }

        foreach ($nodes as $namespace) {
            if (! $namespace instanceof Node\Stmt\Namespace_) {
                continue;
            }
            foreach ($namespace->stmts as $class) {
                if (! $class instanceof Node\Stmt\Class_) {
                    continue;
                }
                foreach ($class->stmts as $property) {
                    $flags = Node\Stmt\Class_::MODIFIER_PROTECTED;
                    $prop = new Node\Stmt\PropertyProperty('connection', new Node\Scalar\String_($this->connection));
                    $class->stmts[] = new Node\Stmt\Property($flags, [$prop]);
                    return null;
                }
            }
        }

        return null;
    }

    protected function shouldRemovedConnection(): bool
    {
        $ref = new ReflectionClass($this->class);

        if (! $ref->getParentClass()) {
            return false;
        }

        $connection = $ref->getParentClass()->getDefaultProperties()['connection'] ?? null;
        return $connection === $this->connection;
    }
}

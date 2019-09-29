<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Database\Commands\Ast;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

class ModelRewriteConnectionVisitor extends NodeVisitorAbstract
{
    /**
     * @var string
     */
    protected $class;

    /**
     * @var string
     */
    protected $connection;

    /**
     * @var bool
     */
    protected $hasConnection = false;

    public function __construct(string $class, string $connection)
    {
        $this->class = $class;
        $this->connection = $connection;
    }

    public function leaveNode(Node $node)
    {
        switch ($node) {
            case $node instanceof Node\Stmt\Property:
                if ($node->props[0]->name == 'connection') {
                    $this->hasConnection = true;

                    if ($this->isRemovedConnection()) {
                        return NodeTraverser::REMOVE_NODE;
                    }

                    $node->props[0]->default = new Node\Scalar\String_($this->connection);
                }

                return $node;
        }
    }

    public function afterTraverse(array $nodes)
    {
        if ($this->hasConnection) {
            return null;
        }

        if ($this->isRemovedConnection()) {
            return null;
        }

        foreach ($nodes as $namespace) {
            if ($namespace instanceof Node\Stmt\Namespace_) {
                foreach ($namespace->stmts as $class) {
                    if ($class instanceof Node\Stmt\Class_) {
                        foreach ($class->stmts as $property) {
                            $flags = Node\Stmt\Class_::MODIFIER_PROTECTED;
                            $prop = new Node\Stmt\PropertyProperty('connection', new Node\Scalar\String_($this->connection));
                            $class->stmts[] = new Node\Stmt\Property($flags, [$prop]);
                            return null;
                        }
                    }
                }
            }
        }
        return null;
    }

    protected function isRemovedConnection(): bool
    {
        $ref = new \ReflectionClass($this->class);
        if ($parent = $ref->getParentClass()) {
            $connection = $parent->getDefaultProperties()['connection'] ?? null;
            if ($connection === $this->connection) {
                return true;
            }
        }

        return false;
    }
}

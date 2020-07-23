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

use Hyperf\Database\Commands\ModelData;
use Hyperf\Database\Commands\ModelOption;
use Hyperf\Database\Model\Model;
use Hyperf\Utils\Collection;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

class ModelRewriteKeyNameVisitor extends NodeVisitorAbstract
{
    /**
     * @var ModelOption
     */
    protected $option;

    /**
     * @var ModelData
     */
    protected $data;

    /**
     * @var Model
     */
    protected $class;

    /**
     * @var bool
     */
    protected $hasPrimaryKey = false;

    public function __construct(ModelOption $option, ModelData $data)
    {
        $this->option = $option;
        $this->data = $data;

        $class = $data->getClass();
        $this->class = new $class();
    }

    public function leaveNode(Node $node)
    {
        switch ($node) {
            case $node instanceof Node\Stmt\Property:
                if ($node->props[0]->name->toLowerString() === 'primarykey') {
                    $this->hasPrimaryKey = true;
                    if (! ($node = $this->rewritePrimaryKey($node))) {
                        return NodeTraverser::REMOVE_NODE;
                    }
                }
                return $node;
        }
    }

    public function afterTraverse(array $nodes)
    {
        if ($this->hasPrimaryKey || $this->shouldRemovedPrimaryKey()) {
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

                foreach ($class->stmts as $key => $node) {
                    if (isset($node->props, $node->props[0], $node->props[0]->name)
                        && $node->props[0]->name->toLowerString() === 'table') {
                        $newNode = $this->rewritePrimaryKey();
                        array_splice($class->stmts, $key, 0, [$newNode]);
                        return null;
                    }
                }
            }
        }
    }

    protected function rewritePrimaryKey(?Node\Stmt\Property $node = null): ?Node\Stmt\Property
    {
        if ($this->shouldRemovedPrimaryKey()) {
            return null;
        }

        $primaryKey = $this->getKeyName();
        if ($node) {
            $node->props[0]->default = new Node\Scalar\String_($primaryKey);
        } else {
            $prop = new Node\Stmt\PropertyProperty('primaryKey', new Node\Scalar\String_($primaryKey));
            $node = new Node\Stmt\Property(Node\Stmt\Class_::MODIFIER_PROTECTED, [$prop]);
        }

        return $node;
    }

    protected function getKeyName(): string
    {
        $columns = Collection::make($this->data->getColumns());
        $column = $columns->where('column_key', 'PRI')->first();

        return $column ? $column['column_name'] : $this->class->getKeyName();
    }

    protected function shouldRemovedPrimaryKey(): bool
    {
        $primaryKey = $this->getKeyName();
        $ref = new \ReflectionClass(get_class($this->class));

        if (! $ref->getParentClass()) {
            return false;
        }

        $parentPrimaryKey = $ref->getParentClass()->getDefaultProperties()['primaryKey'] ?? null;

        return $parentPrimaryKey === $primaryKey;
    }
}

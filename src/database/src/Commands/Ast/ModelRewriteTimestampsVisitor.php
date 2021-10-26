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

class ModelRewriteTimestampsVisitor extends AbstractVisitor
{
    /**
     * @var Model
     */
    protected $class;

    /**
     * @var bool
     */
    protected $hasTimestamps = false;

    public function __construct(ModelOption $option, ModelData $data)
    {
        parent::__construct($option, $data);

        $class = $data->getClass();
        $this->class = new $class();
    }

    public function leaveNode(Node $node)
    {
        switch ($node) {
            case $node instanceof Node\Stmt\Property:
                if ($node->props[0]->name->toLowerString() === 'timestamps') {
                    $this->hasTimestamps = true;
                    if (! ($node = $this->rewriteTimestamps($node))) {
                        return NodeTraverser::REMOVE_NODE;
                    }
                }
                return $node;
        }
    }

    public function afterTraverse(array $nodes)
    {
        if ($this->hasTimestamps || $this->shouldRemovedTimestamps()) {
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
                        $newNode = $this->rewriteTimestamps();
                        array_splice($class->stmts, $key, 0, [$newNode]);
                        return null;
                    }
                }
            }
        }
    }

    protected function rewriteTimestamps(?Node\Stmt\Property $node = null): ?Node\Stmt\Property
    {
        if ($this->shouldRemovedTimestamps()) {
            return null;
        }

        $timestamps = $this->usesTimestamps() ? 'true' : 'false';
        $expr = new Node\Expr\ConstFetch(new Node\Name($timestamps));
        if ($node) {
            $node->props[0]->default = $expr;
        } else {
            $prop = new Node\Stmt\PropertyProperty('timestamps', $expr);
            $node = new Node\Stmt\Property(Node\Stmt\Class_::MODIFIER_PUBLIC, [$prop]);
        }

        return $node;
    }

    protected function usesTimestamps(): bool
    {
        $createdAt = $this->class->getCreatedAtColumn();
        $updatedAt = $this->class->getUpdatedAtColumn();
        $columns = Collection::make($this->data->getColumns());

        return $columns->where('column_name', $createdAt)->count() && $columns->where('column_name', $updatedAt)->count();
    }

    protected function shouldRemovedTimestamps(): bool
    {
        $useTimestamps = $this->usesTimestamps();
        $ref = new \ReflectionClass(get_class($this->class));

        if (! $ref->getParentClass()) {
            return false;
        }

        return $useTimestamps == ($ref->getParentClass()->getDefaultProperties()['timestamps'] ?? null);
    }
}

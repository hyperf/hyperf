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

use Hyperf\Collection\Collection;
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\Stringable\Str;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use ReflectionClass;

class ModelRewriteSoftDeletesVisitor extends AbstractVisitor
{
    protected bool $hasSoftDeletesUse = false;

    protected bool $hasSoftDeletesTraitUse = false;

    protected array $columns = [];

    public function leaveNode(Node $node)
    {
        switch ($node) {
            case $node instanceof Node\Stmt\Use_:
                if ($node->uses[0]->name->toString() === SoftDeletes::class) {
                    $this->hasSoftDeletesUse = true;
                    if (! ($node = $this->rewriteSoftDeletesUse($node))) {
                        return NodeTraverser::REMOVE_NODE;
                    }
                }
                return $node;
            case $node instanceof Node\Stmt\TraitUse:
                foreach ($node->traits as $trait) {
                    if ($trait->toString() === 'SoftDeletes' || Str::endsWith($trait->toString(), '\SoftDeletes')) {
                        $this->hasSoftDeletesTraitUse = true;
                        if (! ($node = $this->rewriteSoftDeletesTraitUse($node))) {
                            return NodeTraverser::REMOVE_NODE;
                        }
                    }
                }
                return $node;
        }

        return null;
    }

    public function afterTraverse(array $nodes)
    {
        foreach ($nodes as $namespace) {
            if (! $namespace instanceof Node\Stmt\Namespace_) {
                continue;
            }

            if (! $this->hasSoftDeletesUse && ($newUse = $this->rewriteSoftDeletesUse())) {
                array_unshift($namespace->stmts, $newUse);
            }

            foreach ($namespace->stmts as $class) {
                if (! $class instanceof Node\Stmt\Class_) {
                    continue;
                }

                if (! $this->hasSoftDeletesTraitUse && ($newTraitUse = $this->rewriteSoftDeletesTraitUse())) {
                    array_unshift($class->stmts, $newTraitUse);
                }
            }
        }
    }

    protected function rewriteSoftDeletesUse(?Node\Stmt\Use_ $node = null): ?Node\Stmt\Use_
    {
        if ($this->shouldRemovedSoftDeletes()) {
            return null;
        }

        if (is_null($node)) {
            $use = new Node\Stmt\UseUse(new Node\Name(SoftDeletes::class));
            $node = new Node\Stmt\Use_([$use]);
        }

        return $node;
    }

    protected function rewriteSoftDeletesTraitUse(?Node\Stmt\TraitUse $node = null): ?Node\Stmt\TraitUse
    {
        if ($this->shouldRemovedSoftDeletes()) {
            return null;
        }

        if (is_null($node)) {
            $node = new Node\Stmt\TraitUse([new Node\Name('SoftDeletes')]);
        }

        return $node;
    }

    protected function useSoftDeletes(): bool
    {
        $model = $this->data->getClass();
        $deletedAt = defined("{$model}::DELETED_AT") ? $model::DELETED_AT : 'deleted_at';
        return Collection::make($this->data->getColumns())->where('column_name', $deletedAt)->count() > 0;
    }

    protected function shouldRemovedSoftDeletes(): bool
    {
        $useSoftDeletes = $this->useSoftDeletes();
        $ref = new ReflectionClass($this->data->getClass());

        if (! $ref->getParentClass()) {
            return false;
        }

        return $useSoftDeletes == $ref->getParentClass()->hasMethod('getDeletedAtColumn');
    }
}

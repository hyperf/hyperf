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
namespace Hyperf\Database\Commands\Ast;

use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\Utils\Str;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

class ModelRewriteTimestampsVisitor extends NodeVisitorAbstract
{
    use SoftDeletes;

    /**
     * @var Model
     */
    protected $class;

    /**
     * @var bool
     */
    protected $hasTimestamps = false;

    /**
     * @var bool
     */
    protected $hasSoftDeletesUse = false;

    /**
     * @var bool
     */
    protected $hasSoftDeletesTraitUse = false;

    /**
     * @var array
     */
    protected $columns = [];

    public function __construct(string $class, array $columns)
    {
        $this->class = new $class();
        $this->columns = $columns;
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

                foreach ($class->stmts as $key => $node) {
                    if (isset($node->props, $node->props[0], $node->props[0]->name)
                        && $node->props[0]->name->toLowerString() === 'table') {
                        if (! $this->hasTimestamps && ($newNode = $this->rewriteTimestamps())) {
                            array_splice($class->stmts, $key, 0, [$newNode]);
                        }
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
        $columns = Collection::make($this->columns);

        return $columns->where('column_name', $createdAt)->count() && $columns->where('column_name', $updatedAt)->count();
    }

    protected function shouldRemovedTimestamps(): bool
    {
        $useTimestamps = $this->usesTimestamps();
        $ref = new \ReflectionClass(get_class($this->class));

        if (! $ref->getParentClass()) {
            return false;
        }

        $parentTimestamps = $ref->getParentClass()->getDefaultProperties()['timestamps'] ?? null;

        return $parentTimestamps === $useTimestamps;
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
        $deletedAt = $this->getDeletedAtColumn();
        if (method_exists($this->class, 'getDeletedAtColumn')) {
            $deletedAt = $this->class->getDeletedAtColumn();
        }

        return Collection::make($this->columns)->where('column_name', $deletedAt)->count() > 0;
    }

    protected function shouldRemovedSoftDeletes(): bool
    {
        $useSoftDeletes = $this->useSoftDeletes();
        $ref = new \ReflectionClass(get_class($this->class));

        if (! $ref->getParentClass()) {
            return false;
        }

        $parentSoftDeletes = $ref->getParentClass()->hasMethod('getDeletedAtColumn') ?? null;

        return $parentSoftDeletes === $useSoftDeletes;
    }
}

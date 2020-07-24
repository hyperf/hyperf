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
use Hyperf\Utils\Arr;
use Hyperf\Utils\Collection;
use Hyperf\Utils\Str;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

class ModelRewriteKeyInfoVisitor extends NodeVisitorAbstract
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
     * @var bool
     */
    protected $hasPrimaryKey = false;

    /**
     * @var bool
     */
    protected $hasKeyType = false;

    /**
     * @var bool
     */
    protected $hasIncrementing = false;

    public function __construct(ModelOption $option, ModelData $data)
    {
        $this->option = $option;
        $this->data = $data;
    }

    public function leaveNode(Node $node)
    {
        switch ($node) {
            case $node instanceof Node\Stmt\Property:
                if ($node->props[0]->name->toLowerString() === 'primarykey') {
                    $this->hasPrimaryKey = true;
                    if (! ($node = $this->rewrite('primaryKey', $node))) {
                        return NodeTraverser::REMOVE_NODE;
                    }
                }
                if ($node->props[0]->name->toLowerString() === 'keytype') {
                    $this->hasKeyType = true;
                    if (! ($node = $this->rewrite('keyType', $node))) {
                        return NodeTraverser::REMOVE_NODE;
                    }
                }
                if ($node->props[0]->name->toLowerString() === 'incrementing') {
                    $this->hasIncrementing = true;
                    if (! ($node = $this->rewrite('incrementing', $node))) {
                        return NodeTraverser::REMOVE_NODE;
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

            foreach ($namespace->stmts as $class) {
                if (! $class instanceof Node\Stmt\Class_) {
                    continue;
                }

                foreach ($class->stmts as $key => $node) {
                    if (isset($node->props, $node->props[0], $node->props[0]->name)
                        && $node->props[0]->name->toLowerString() === 'table') {
                        if (! $this->hasKeyType && $newNode = $this->rewrite('keyType')) {
                            array_splice($class->stmts, $key, 0, [$newNode]);
                        }
                        if (! $this->hasPrimaryKey && $newNode = $this->rewrite('primaryKey')) {
                            array_splice($class->stmts, $key, 0, [$newNode]);
                        }
                        if (! $this->hasIncrementing && $newNode = $this->rewrite('incrementing')) {
                            array_splice($class->stmts, $key, 0, [$newNode]);
                        }
                        return null;
                    }
                }
            }
        }
    }

    protected function rewrite($property = 'primaryKey', ?Node\Stmt\Property $node = null): ?Node\Stmt\Property
    {
        $data = $this->getKeyInfo();
        if ($data === null) {
            return $node;
        }

        [$primaryKey, $keyType, $incrementing] = $data;

        if ($this->shouldRemoveProperty($property, ${$property})) {
            return null;
        }

        if ($node) {
            $node->props[0]->default = $this->getExpr($property, ${$property});
        } else {
            $prop = new Node\Stmt\PropertyProperty($property, $this->getExpr($property, ${$property}));
            $node = new Node\Stmt\Property(
                $property == 'incrementing' ? Node\Stmt\Class_::MODIFIER_PUBLIC : Node\Stmt\Class_::MODIFIER_PROTECTED,
                [$prop]
            );
        }

        return $node;
    }

    /**
     * @param bool|string $value
     * @return Node\Scalar
     */
    protected function getExpr(string $property, $value): Node\Expr
    {
        if ($property == 'incrementing') {
            return new Node\Expr\ConstFetch(new Node\Name([$value ? 'true' : 'false']));
        }

        return new Node\Scalar\String_($value);
    }

    protected function getKeyInfo(): ?array
    {
        $columns = Collection::make($this->data->getColumns());
        $column = $columns->where('column_key', 'PRI')->first();

        if ($column) {
            $name = Arr::get($column, 'column_name');
            $type = Str::endsWith(Arr::get($column, 'data_type', 'int'), 'int') ? 'int' : 'string';
            $increment = Arr::get($column, 'extra') == 'auto_increment';
            return [$name, $type, $increment];
        }

        return null;
    }

    protected function shouldRemoveProperty($property, $value): bool
    {
        $ref = new \ReflectionClass($this->data->getClass());

        if (! $ref->getParentClass()) {
            return false;
        }

        return $value == ($ref->getParentClass()->getDefaultProperties()[$property] ?? null);
    }
}

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

use Hyperf\Collection\Arr;
use Hyperf\Collection\Collection;
use Hyperf\Stringable\Str;
use InvalidArgumentException;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\NodeTraverser;
use ReflectionClass;

class ModelRewriteKeyInfoVisitor extends AbstractVisitor
{
    protected bool $hasPrimaryKey = false;

    protected bool $hasKeyType = false;

    protected bool $hasIncrementing = false;

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

        return null;
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

        return null;
    }

    protected function rewrite(string $property, ?Node\Stmt\Property $node = null): ?Node\Stmt\Property
    {
        $data = $this->getKeyInfo();
        if ($data === null) {
            return $node;
        }

        $propertyValue = match ($property) {
            'primaryKey' => $data[0],
            'keyType' => $data[1],
            'incrementing' => $data[2],
            default => throw new InvalidArgumentException("property {$property} is invalid.")
        };

        if ($this->shouldRemoveProperty($property, $propertyValue)) {
            return null;
        }

        if ($node) {
            $node->props[0]->default = $this->getExpr($property, $propertyValue);
        } else {
            $prop = new Node\Stmt\PropertyProperty($property, $this->getExpr($property, $propertyValue));
            $node = new Node\Stmt\Property(
                $property == 'incrementing' ? Node\Stmt\Class_::MODIFIER_PUBLIC : Node\Stmt\Class_::MODIFIER_PROTECTED,
                [$prop]
            );
            $node->type = match ($property) {
                'incrementing' => new Identifier('bool'),
                default => new Identifier('string'),
            };
        }

        return $node;
    }

    /**
     * @param bool|string $value
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
        $ref = new ReflectionClass($this->data->getClass());

        if (! $ref->getParentClass()) {
            return false;
        }

        return $value == ($ref->getParentClass()->getDefaultProperties()[$property] ?? null);
    }
}

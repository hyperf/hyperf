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

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class ModelUpdateVistor extends NodeVisitorAbstract
{
    protected $columns = [];

    protected $forceCasts = false;

    public function __construct($columns = [], bool $forceCasts)
    {
        $this->columns = $columns;
        $this->forceCasts = $forceCasts;
    }

    public function leaveNode(Node $node)
    {
        switch ($node) {
            case $node instanceof Node\Stmt\PropertyProperty:
                if ($node->name == 'fillable') {
                    $node = $this->rewriteFillable($node);
                } elseif ($node->name == 'casts') {
                    $node = $this->rewriteCasts($node);
                }

                return $node;
            case $node instanceof Node\Stmt\Class_:
                $doc = '/**' . PHP_EOL;
                foreach ($this->columns as $column) {
                    $name = $column['column_name'];
                    $doc .= ' * @property $' . $name . PHP_EOL;
                }
                $doc .= ' */';
                $node->setDocComment(new Doc($doc));
                return $node;
        }
    }

    protected function rewriteFillable(Node\Stmt\PropertyProperty $node): Node\Stmt\PropertyProperty
    {
        $items = [];
        foreach ($this->columns as $column) {
            $items[] = new Node\Expr\ArrayItem(new Node\Scalar\String_($column['column_name']));
        }

        $node->default = new Node\Expr\Array_($items, [
            'kind' => Node\Expr\Array_::KIND_SHORT,
        ]);
        return $node;
    }

    protected function rewriteCasts(Node\Stmt\PropertyProperty $node): Node\Stmt\PropertyProperty
    {
        $items = [];
        $exists = [];

        /** @var Node\Expr\ArrayItem $item */
        foreach ($node->default->items as $item) {
            $exists[] = $item->key->value;
            $items[] = $item;
        }

        if ($this->forceCasts) {
            $items = [];
        }

        foreach ($this->columns as $column) {
            $name = $column['column_name'];
            $type = $this->formatDatabaseType($column['data_type']);

            if ($type && (! in_array($name, $exists) || $this->forceCasts)) {
                $items[] = new Node\Expr\ArrayItem(
                    new Node\Scalar\String_($type),
                    new Node\Scalar\String_($name)
                );
            }
        }

        $node->default = new Node\Expr\Array_($items, [
            'kind' => Node\Expr\Array_::KIND_SHORT,
        ]);
        return $node;
    }

    protected function formatDatabaseType(string $type): ?string
    {
        switch ($type) {
            case 'tinyint':
            case 'smallint':
            case 'mediumint':
            case 'int':
            case 'bigint':
                return 'integer';
            case 'decimal':
            case 'float':
            case 'double':
            case 'real':
                return 'float';
            case 'bool':
            case 'boolean':
                return 'boolean';
            default:
                return null;
        }
    }
}

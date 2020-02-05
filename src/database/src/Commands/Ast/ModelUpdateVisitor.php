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

use Hyperf\Database\Commands\ModelOption;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\NodeVisitorAbstract;

class ModelUpdateVisitor extends NodeVisitorAbstract
{
    /**
     * @var array
     */
    protected $columns = [];

    /**
     * @var ModelOption
     */
    protected $option;

    /**
     * @var string
     */
    protected $class = '';

    public function __construct($columns = [], ModelOption $option, $class)
    {
        $this->columns = $columns;
        $this->option = $option;
        $this->class = $class;
    }

    public function leaveNode(Node $node)
    {
        switch ($node) {
            case $node instanceof Node\Stmt\PropertyProperty:
                if ($node->name == 'fillable' && $this->option->isRefreshFillable()) {
                    $node = $this->rewriteFillable($node);
                } elseif ($node->name == 'casts') {
                    $node = $this->rewriteCasts($node);
                }

                return $node;
            case $node instanceof Node\Stmt\Class_:
                //更改模型继承的父类名;
                $inheritance = $this->option->getInheritance();
                if(is_object($node->extends) && !empty($inheritance)) {
                    $node->extends->parts = [$inheritance];
                }

                $doc = '/**' . PHP_EOL;
                foreach ($this->columns as $column) {
                    [$name, $type, $comment] = $this->getProperty($column);
                    $doc .= sprintf(' * @property %s $%s %s', $type, $name, $comment) . PHP_EOL;
                }
                $doc .= ' */';
                $node->setDocComment(new Doc($doc));
                return $node;
            case $node instanceof Node\Stmt\UseUse: //更改模型父类的use路径;
                $modelParent = get_parent_class($this->class);

                $class = end($node->name->parts);
                $alias = is_object($node->alias) ? $node->alias->name : '';
                if ($class == $modelParent || $alias = $modelParent) {
                    preg_match_all('/\s*([a-z0-9\\\\]+)(as)?([a-z0-9]+)?;?\s*/is', $this->option->getUses(), $match);
                    if(!empty($match) && isset($match[1][0])) {
                        $newClass = $match[1][0];
                        $newAlias = $match[1][2] ?? '';

                        $node->name->parts = explode('\\', $newClass);
                        $node->alias = null;

                        if(!empty($newAlias)) {
                            $node->alias = new Identifier($newAlias);
                            $node->alias->setAttribute('startLine', $node->getAttribute('startLine'));
                            $node->alias->setAttribute('endLine', $node->getAttribute('endLine'));
                        }
                    }
                }
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
        foreach ($this->columns as $column) {
            $name = $column['column_name'];
            $type = $column['cast'] ?? null;
            if ($type || $type = $this->formatDatabaseType($column['data_type'])) {
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

    protected function getProperty($column): array
    {
        $name = $column['column_name'];

        $type = $this->formatPropertyType($column['data_type'], $column['cast'] ?? null);

        $comment = $this->option->isWithComments() ? $column['column_comment'] ?? '' : '';

        return [$name, $type, $comment];
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

    protected function formatPropertyType(string $type, ?string $cast): ?string
    {
        if (! isset($cast)) {
            $cast = $this->formatDatabaseType($type) ?? 'string';
        }

        switch ($cast) {
            case 'integer':
                return 'int';
            case 'date':
            case 'datetime':
                return '\Carbon\Carbon';
            case 'json':
                return 'array';
        }

        return $cast;
    }
}

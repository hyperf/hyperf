<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Database\Commands\Ast;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Name;
use PhpParser\NodeVisitorAbstract;

class ModelUpdateVistor extends NodeVisitorAbstract
{
    protected $columns = [];

    public function __construct($columns = [])
    {
        $this->columns = $columns;
    }

    public function leaveNode(Node $node)
    {
        switch ($node) {
            case $node instanceof Node\Stmt\PropertyProperty:
                if ('fillable' == $node->name) {
                    $node = $this->rewriteFillable($node);
                }

                return $node;
            case $node instanceof StaticPropertyFetch && $this->extends:
                // Rewrite parent::$staticProperty to ParentClass::$staticProperty.
                if ($node->class && 'parent' === $node->class->toString()) {
                    $node->class = new Name($this->extends->toCodeString());
                    return $node;
                }
        }
    }

    protected function rewriteFillable(Node\Stmt\PropertyProperty $node)
    {
        $items = [];
        foreach ($this->columns as $column) {
            $items[] = new Node\Expr\ArrayItem(new Node\Scalar\String_($column));
        }

        $node->default = new Node\Expr\Array_($items);
        return $node;
    }
}

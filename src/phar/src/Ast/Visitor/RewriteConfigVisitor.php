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
namespace Hyperf\Phar\Ast\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class RewriteConfigVisitor extends NodeVisitorAbstract
{
    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Return_) {
            if (! $node->expr instanceof Node\Expr\Array_) {
                return $node;
            }
            foreach ($node->expr->items as $item) {
                if (! $item instanceof Node\Expr\ArrayItem) {
                    continue;
                }
                if ($item->key instanceof Node\Scalar\String_ && strtolower($item->key->value) == 'scan_cacheable') {
                    $item->value = new Node\Expr\ConstFetch(new Node\Name('true'));
                }
            }
        }
        return $node;
    }
}

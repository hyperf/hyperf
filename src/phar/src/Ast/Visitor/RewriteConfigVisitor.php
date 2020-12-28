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
            $result = new Node\Expr\Variable('result');
            $assign = new Node\Expr\Assign($result, $node->expr);
            return new Node\Stmt\Expression($assign);
        }
        return $node;
    }
}

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
    public function leaveNode(Node $node): Node|Node\Stmt\Expression
    {
        if ($node instanceof Node\Stmt\Return_) {
            $result = new Node\Expr\Variable('result');
            $assign = new Node\Expr\Assign($result, $node->expr);
            return new Node\Stmt\Expression($assign);
        }
        return $node;
    }

    public function afterTraverse(array $nodes): array
    {
        $nodes[] = $this->createReturn();
        return $nodes;
    }

    protected function createReturn(): Node\Stmt\Return_
    {
        $funcCall = new Node\Expr\FuncCall(new Node\Name('array_replace'));
        $funcCall->args = [
            new Node\Arg(new Node\Expr\Variable('result')),
            $this->createScanArg(),
        ];
        return new Node\Stmt\Return_($funcCall);
    }

    protected function createScanArg(): Node\Arg
    {
        $array = new Node\Expr\Array_();
        $array->items[] = new Node\Expr\ArrayItem(
            new Node\Expr\ConstFetch(new Node\Name('true')),
            new Node\Scalar\String_('scan_cacheable')
        );
        return new Node\Arg($array);
    }
}

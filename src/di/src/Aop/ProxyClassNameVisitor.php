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

namespace Hyperf\Di\Aop;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class ProxyClassNameVisitor extends NodeVisitorAbstract
{
    /**
     * @var string
     */
    private $proxyClassName;

    public function __construct(string $proxyClassName)
    {
        if (strpos($proxyClassName, '\\') !== false) {
            $exploded = explode('\\', $proxyClassName);
            $proxyClassName = end($exploded);
        }
        $this->proxyClassName = $proxyClassName;
    }

    public function leaveNode(Node $node)
    {
        // Rewirte the class name and extends the original class.
        if ($node instanceof Node\Stmt\Class_ && ! $node->isAnonymous()) {
            $node->extends = new Node\Name($node->name->name);
            $node->name = new Node\Identifier($this->proxyClassName);
            return $node;
        }
    }
}

<?php

namespace Hyperf\Di\Aop;


use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class ProxyClassNameVistor extends NodeVisitorAbstract
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
        if ($node instanceof Node\Stmt\Class_) {
            $node->extends = $node->name;
            $node->name = new Node\Identifier($this->proxyClassName);
            return $node;
        }
    }

}
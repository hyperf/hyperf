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
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\NodeVisitorAbstract;

class ModelRewriteInheritanceVisitor extends NodeVisitorAbstract
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
     * @var null|string
     */
    protected $parentClass;

    /**
     * @var bool
     */
    protected $shouldAddUseUse = true;

    public function __construct(ModelOption $option, ModelData $data)
    {
        $this->option = $option;
        $this->data = $data;

        if (! empty($option->getUses())) {
            preg_match_all('/\s*([a-z0-9\\\\]+)(as)?([a-z0-9]+)?;?\s*/is', $option->getUses(), $match);
            if (isset($match[1][0])) {
                $this->parentClass = $match[1][0];
            }
        }
    }

    public function afterTraverse(array $nodes)
    {
        if (empty($this->option->getUses())) {
            return null;
        }

        $use = new Node\Stmt\UseUse(
            new Node\Name($this->parentClass),
            $this->option->getInheritance()
        );

        foreach ($nodes as $namespace) {
            if (! $namespace instanceof Node\Stmt\Namespace_) {
                continue;
            }

            if ($this->shouldAddUseUse) {
                array_unshift($namespace->stmts, new Node\Stmt\Use_([$use]));
            }
        }

        return null;
    }

    public function leaveNode(Node $node)
    {
        switch ($node) {
            case $node instanceof Node\Stmt\Class_:
                $inheritance = $this->option->getInheritance();
                if (is_object($node->extends) && ! empty($inheritance)) {
                    $node->extends->parts = [$inheritance];
                }
                return $node;
            case $node instanceof Node\Stmt\UseUse:
                $class = implode('\\', $node->name->parts);
                $alias = is_object($node->alias) ? $node->alias->name : null;
                if ($class == $this->parentClass) {
                    // The parent class is exists.
                    $this->shouldAddUseUse = false;
                    if (end($node->name->parts) !== $this->option->getInheritance() && $alias !== $this->option->getInheritance()) {
                        // Rewrite the alias, if the class is not equal with inheritance.
                        $node->alias = new Identifier($this->option->getInheritance());
                    }
                }
                return $node;
        }
    }
}

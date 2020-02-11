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

    public function __construct(ModelOption $option, ModelData $data)
    {
        $this->option = $option;
        $this->data = $data;
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
                $modelParent = get_parent_class($this->data->getClass());

                $class = end($node->name->parts);
                $alias = is_object($node->alias) ? $node->alias->name : '';
                if ($class == $modelParent || $alias == $modelParent) {
                    preg_match_all('/\s*([a-z0-9\\\\]+)(as)?([a-z0-9]+)?;?\s*/is', $this->option->getUses(), $match);
                    if (! empty($match) && isset($match[1][0])) {
                        $newClass = $match[1][0];
                        $newAlias = $match[1][2] ?? '';

                        $node->name->parts = explode('\\', $newClass);
                        $node->alias = null;

                        if (! empty($newAlias)) {
                            $node->alias = new Identifier($newAlias);
                            $node->alias->setAttribute('startLine', $node->getAttribute('startLine'));
                            $node->alias->setAttribute('endLine', $node->getAttribute('endLine'));
                        }
                    }
                }
                return $node;
        }
    }
}

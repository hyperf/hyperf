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

use Hyperf\CodeParser\PhpParser;
use Hyperf\Database\Commands\ModelData;
use Hyperf\Database\Commands\ModelOption;
use Hyperf\Stringable\Str;
use PhpParser\Node;

use function Hyperf\Support\getter;
use function Hyperf\Support\setter;

class ModelRewriteGetterSetterVisitor extends AbstractVisitor
{
    /**
     * @var string[]
     */
    protected array $getters = [];

    /**
     * @var string[]
     */
    protected array $setters = [];

    public function __construct(ModelOption $option, ModelData $data)
    {
        parent::__construct($option, $data);
    }

    public function beforeTraverse(array $nodes)
    {
        $methods = PhpParser::getInstance()->getAllMethodsFromStmts($nodes);

        $this->collectMethods($methods);

        return null;
    }

    public function afterTraverse(array $nodes)
    {
        foreach ($nodes as $namespace) {
            if (! $namespace instanceof Node\Stmt\Namespace_) {
                continue;
            }

            foreach ($namespace->stmts as $class) {
                if (! $class instanceof Node\Stmt\Class_) {
                    continue;
                }

                array_push($class->stmts, ...$this->buildGetterAndSetter());
            }
        }

        return $nodes;
    }

    /**
     * @return Node\Stmt\ClassMethod[]
     */
    protected function buildGetterAndSetter(): array
    {
        $stmts = [];
        foreach ($this->data->getColumns() as $column) {
            if ($name = $column['column_name'] ?? null) {
                $getter = getter($name);
                if (! in_array($getter, $this->getters)) {
                    $stmts[] = $this->createGetter($getter, $name);
                }
                $setter = setter($name);
                if (! in_array($setter, $this->setters)) {
                    $stmts[] = $this->createSetter($setter, $name);
                }
            }
        }

        return $stmts;
    }

    protected function createGetter(string $method, string $name): Node\Stmt\ClassMethod
    {
        $node = new Node\Stmt\ClassMethod($method, ['flags' => Node\Stmt\Class_::MODIFIER_PUBLIC]);
        $node->stmts[] = new Node\Stmt\Return_(
            new Node\Expr\PropertyFetch(
                new Node\Expr\Variable('this'),
                new Node\Identifier($name)
            )
        );

        return $node;
    }

    protected function createSetter(string $method, string $name): Node\Stmt\ClassMethod
    {
        $node = new Node\Stmt\ClassMethod($method, [
            'flags' => Node\Stmt\Class_::MODIFIER_PUBLIC,
            'params' => [new Node\Param(new Node\Expr\Variable($name))],
        ]);
        $node->stmts[] = new Node\Stmt\Expression(
            new Node\Expr\Assign(
                new Node\Expr\PropertyFetch(
                    new Node\Expr\Variable('this'),
                    new Node\Identifier($name)
                ),
                new Node\Expr\Variable($name)
            )
        );
        $node->stmts[] = new Node\Stmt\Return_(
            new Node\Expr\Variable('this')
        );

        return $node;
    }

    protected function collectMethods(array $methods)
    {
        /** @var Node\Stmt\ClassMethod $method */
        foreach ($methods as $method) {
            $methodName = $method->name->name;
            if (Str::startsWith($methodName, 'get')) {
                $this->getters[] = $methodName;
            } elseif (Str::startsWith($methodName, 'set')) {
                $this->setters[] = $methodName;
            }
        }
    }
}

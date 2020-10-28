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
use Hyperf\Database\Model\Builder;
use Hyperf\Utils\Str;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;

class GenerateModelIDEVisitor extends AbstractVisitor
{
    /**
     * @var array
     */
    protected $methods = [];

    /**
     * @var null|Node\Stmt\Namespace_
     */
    protected $namespace;

    /**
     * @var null|Node\Stmt\Class_
     */
    protected $class;

    public function __construct(ModelOption $option, ModelData $data)
    {
        parent::__construct($option, $data);

        $this->initPropertiesFromMethods();
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Namespace_) {
            $this->namespace = new Node\Stmt\Namespace_($node->name);
        }

        if ($node instanceof Node\Stmt\Class_) {
            $this->class = new Node\Stmt\Class_($node->name);
        }
    }

    public function afterTraverse(array $nodes)
    {
        $builder = new Node\Stmt\Property(
            Node\Stmt\Class_::MODIFIER_PUBLIC | Node\Stmt\Class_::MODIFIER_STATIC,
            [new Node\Stmt\PropertyProperty('builder')]
        );
        $doc = '/**' . PHP_EOL;
        $doc .= ' * @var \Hyperf\Database\Model\Builder' . PHP_EOL;
        $doc .= ' */';
        $builder->setDocComment(new Doc($doc));
        $this->class->stmts[] = $builder;
        $doc = '/**' . PHP_EOL;
        $doc .= ' * @return \Hyperf\Database\Model\Builder|static' . PHP_EOL;
        $doc .= ' */';
        foreach ($this->data->getColumns() as $column) {
            $name = Str::camel('where_' . $column['column_name']);
            $method = new Node\Stmt\ClassMethod($name, [
                'flags' => Node\Stmt\Class_::MODIFIER_PUBLIC | Node\Stmt\Class_::MODIFIER_STATIC,
                'params' => [new Node\Param(new Node\Expr\Variable('value'))],
            ]);
            $method->setDocComment(new Doc($doc));
            $method->stmts[] = new Node\Stmt\Return_(
                new Node\Expr\MethodCall(
                    new Node\Expr\StaticPropertyFetch(
                        new Node\Name('static'),
                        new Node\VarLikeIdentifier('builder')
                    ),
                    new Node\Identifier('dynamicWhere'),
                    [
                        new Node\Arg(new Node\Scalar\String_($name)),
                        new Node\Arg(new Node\Expr\Variable('value')),
                    ]
                )
            );
            $this->class->stmts[] = $method;
        }
        $this->namespace->stmts = [$this->class];
        return [$this->namespace];
    }

    protected function setMethod(string $name, array $type = [], array $arguments = [])
    {
        $methods = array_change_key_case($this->methods, CASE_LOWER);

        if (! isset($methods[strtolower($name)])) {
            $this->methods[$name] = [];
            $this->methods[$name]['type'] = implode('|', $type);
            $this->methods[$name]['arguments'] = $arguments;
        }
    }

    protected function initPropertiesFromMethods()
    {
        /** @var ReflectionClass $reflection */
        $reflection = BetterReflectionManager::getReflector()->reflect($this->data->getClass());
        $methods = $reflection->getImmediateMethods();

        sort($methods);
        /** @var ReflectionMethod $method */
        foreach ($methods as $method) {
            if (Str::startsWith($method->getName(), 'scope') && $method->getName() !== 'scopeQuery') {
                $name = Str::camel(substr($method->getName(), 5));
                if (! empty($name)) {
                    $args = $method->getParameters();
                    // Remove the first ($query) argument
                    array_shift($args);
                    $this->setMethod($name, [Builder::class, $method->getDeclaringClass()->getName()], $args);
                }
                continue;
            }

            if ($method->getNumberOfParameters() > 0) {
                continue;
            }
        }
    }

    protected function parseScopeMethod(string $doc): string
    {
        foreach ($this->methods as $name => $method) {
            $doc .= sprintf(
                ' * @method static %s|%s %s()' . PHP_EOL,
                '\\' . Builder::class,
                '\\' . get_class($this->class),
                $name
            ) . PHP_EOL;
        }
        return $doc;
    }
}

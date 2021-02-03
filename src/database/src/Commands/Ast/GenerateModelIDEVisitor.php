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
use Roave\BetterReflection\Reflection\ReflectionParameter;

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

    /**
     * @var string
     */
    protected $nsp = '';

    public function __construct(ModelOption $option, ModelData $data)
    {
        parent::__construct($option, $data);

        $this->initPropertiesFromMethods();
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Namespace_) {
            $this->namespace = new Node\Stmt\Namespace_();
            $this->nsp = $node->name->toString();
        }

        if ($node instanceof Node\Stmt\Class_) {
            $this->class = new Node\Stmt\Class_(
                new Node\Identifier(self::toIDEClass($this->nsp . '\\' . $node->name->toString()))
            );
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
        $scopeDoc = '/**' . PHP_EOL;
        $scopeDoc .= ' * @return \Hyperf\Database\Model\Builder|static' . PHP_EOL;
        $scopeDoc .= ' */';
        foreach ($this->methods as $name => $call) {
            $params = [];
            /** @var ReflectionParameter $argument */
            foreach ($call['arguments'] as $argument) {
                $argName = new Node\Expr\Variable($argument->getName());
                if ($argument->hasType()) {
                    if ($argument->getType()->allowsNull()) {
                        $argType = new Node\NullableType($argument->getType()->getName());
                    } else {
                        $argType = $argument->getType()->getName();
                    }
                }
                $params[] = new Node\Param(
                    $argName,
                    $argument->getAst()->default ?? null,
                    $argType ?? null
                );
            }
            $method = new Node\Stmt\ClassMethod($name, [
                'flags' => Node\Stmt\Class_::MODIFIER_PUBLIC | Node\Stmt\Class_::MODIFIER_STATIC,
                'params' => $params,
            ]);
            $method->setDocComment(new Doc($scopeDoc));
            $method->stmts[] = new Node\Stmt\Return_(
                new Node\Expr\StaticPropertyFetch(
                    new Node\Name('static'),
                    new Node\VarLikeIdentifier('builder')
                )
            );
            $this->class->stmts[] = $method;
        }
        $this->namespace->stmts = [$this->class];
        return [$this->namespace];
    }

    public static function toIDEClass(string $class): string
    {
        return str_replace('\\', '_', $class);
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
}

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
namespace Hyperf\Swagger\Command\Ast;

use Hyperf\Database\Model\Model;
use Hyperf\Database\Schema\Builder;
use Hyperf\Database\Schema\Column;
use Hyperf\Stringable\Str;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use ReflectionClass;

class ModelSchemaVisitor extends NodeVisitorAbstract
{
    /**
     * @var Column[]
     */
    public array $columns = [];

    public function __construct(public ReflectionClass $ref, public Model $model)
    {
        /** @var Builder $builder */
        $builder = $this->model->getConnection()->getSchemaBuilder();

        $this->columns = array_filter($builder->getColumns(), function (Column $column) {
            return $column->getTable() === $this->model->getTable();
        });
    }

    public function beforeTraverse(array $nodes)
    {
        return null;
    }

    public function leaveNode(Node $node)
    {
        switch ($node) {
            case $node instanceof Node\Stmt\Class_:
                $node->stmts = array_merge([], $this->buildProperties());
                $node->stmts[] = $this->buildConstructor();
                $node->stmts[] = $this->buildJsonSerialize();
                return $node;
        }

        return null;
    }

    public function buildJsonSerialize(): Node\Stmt\ClassMethod
    {
        $items = [];
        foreach ($this->columns as $column) {
            $items[] = new Node\Expr\ArrayItem(
                new Node\Expr\PropertyFetch(
                    new Node\Expr\Variable('this'),
                    new Node\Identifier(Str::camel($column->getName())),
                ),
                new Node\Scalar\String_($column->getName())
            );
        }

        return new Node\Stmt\ClassMethod(new Node\Identifier('jsonSerialize'), [
            'flags' => Node\Stmt\Class_::MODIFIER_PUBLIC,
            'returnType' => new Node\Identifier('mixed'),
            'stmts' => [new Node\Stmt\Return_(new Node\Expr\Array_($items, [
                'kind' => Node\Expr\Array_::KIND_SHORT,
            ]))],
        ]);
    }

    public function buildConstructor(): Node\Stmt\ClassMethod
    {
        $method = new Node\Stmt\ClassMethod(new Node\Identifier('__construct'), [
            'flags' => Node\Stmt\Class_::MODIFIER_PUBLIC,
            'params' => [
                new Node\Param(
                    type: new Node\Identifier('\\' . $this->ref->getName()),
                    var: new Node\Expr\Variable('model'),
                ),
            ],
        ]);

        foreach ($this->columns as $column) {
            $method->stmts[] = new Node\Stmt\Expression(
                new Node\Expr\Assign(
                    new Node\Expr\PropertyFetch(
                        new Node\Expr\Variable('this'),
                        new Node\Identifier(Str::camel($column->getName())),
                    ),
                    new Node\Expr\PropertyFetch(
                        new Node\Expr\Variable('model'),
                        new Node\Identifier($column->getName()),
                    ),
                )
            );
        }

        return $method;
    }

    public function buildProperties(): array
    {
        $result = [];
        /** @var Column $column */
        foreach ($this->columns as $column) {
            $result[] = new Node\Stmt\Property(
                Node\Stmt\Class_::MODIFIER_PUBLIC,
                [
                    new Node\Stmt\PropertyProperty(
                        new Node\VarLikeIdentifier(Str::camel($column->getName()))
                    ),
                ],
                type: new Node\Identifier(name: $this->formatDatabaseType($column->getType(), true)),
                attrGroups: [
                    new Node\AttributeGroup([
                        new Node\Attribute(new Node\Name('Property'), [
                            new Node\Arg(value: new Node\Scalar\String_($column->getName()), name: new Node\Identifier('property')),
                            new Node\Arg(value: new Node\Scalar\String_($column->getComment()), name: new Node\Identifier('title')),
                            new Node\Arg(value: new Node\Scalar\String_($this->formatDatabaseType($column->getType())), name: new Node\Identifier('type')),
                        ]),
                    ]),
                ]
            );
        }

        return $result;
    }

    protected function formatDatabaseType(string $type, bool $nullable = false): ?string
    {
        return match ($type) {
            'tinyint', 'smallint', 'mediumint', 'int', 'bigint' => $nullable ? '?int' : 'int',
            'bool', 'boolean' => $nullable ? '?bool' : 'bool',
            'varchar', 'char' => $nullable ? '?string' : 'string',
            default => 'mixed',
        };
    }
}

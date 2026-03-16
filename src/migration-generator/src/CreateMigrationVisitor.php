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

namespace Hyperf\MigrationGenerator;

use Hyperf\CodeParser\PhpParser;
use Hyperf\Collection\Collection;
use Hyperf\Database\Commands\ModelOption;
use Hyperf\Database\Schema\Column;
use Hyperf\Stringable\Str;
use InvalidArgumentException;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

use function Hyperf\Support\value;

class CreateMigrationVisitor extends NodeVisitorAbstract
{
    /**
     * @param Collection<int, Column> $columns
     */
    public function __construct(private string $table, private ModelOption $option, private Collection $columns, private TableData $tableData)
    {
    }

    public function afterTraverse(array $nodes)
    {
        foreach ($nodes as $class) {
            if ($class instanceof Node\Stmt\Class_) {
                $class->name = new Node\Identifier(Str::studly('create_' . Str::snake($this->table) . '_table'));
                $upStmt = new Node\Stmt\ClassMethod('up', [
                    'returnType' => new Node\Identifier('void'),
                    'flags' => Node\Stmt\Class_::MODIFIER_PUBLIC | Node\Stmt\Class_::MODIFIER_FINAL,
                    'stmts' => [
                        new Node\Stmt\Expression(
                            new Node\Expr\StaticCall(
                                new Node\Name('Schema'),
                                new Node\Identifier('create'),
                                [
                                    new Node\Arg(new Node\Scalar\String_($this->table)),
                                    new Node\Arg(new Node\Expr\Closure([
                                        'params' => [
                                            new Node\Param(
                                                new Node\Expr\Variable('table'),
                                                null,
                                                new Node\Name('Blueprint')
                                            ),
                                        ],
                                        'stmts' => value(function () {
                                            $result = [];
                                            $isAutoIncrement = false;
                                            foreach ($this->columns as $column) {
                                                if (! $isAutoIncrement) {
                                                    $isAutoIncrement = $this->isAutoIncrement($column);
                                                }

                                                $result[] = $this->createStmtFromColumn($column);
                                            }

                                            return array_merge(
                                                $result,
                                                $this->createStmtFromIndexes($isAutoIncrement),
                                                $this->createStmtFromComment()
                                            );
                                        }),
                                    ])),
                                ]
                            )
                        ),
                    ],
                ]);
                $downStmt = new Node\Stmt\ClassMethod('down', [
                    'returnType' => new Node\Identifier('void'),
                    'flags' => Node\Stmt\Class_::MODIFIER_PUBLIC | Node\Stmt\Class_::MODIFIER_FINAL,
                    'stmts' => [
                        new Node\Stmt\Expression(
                            new Node\Expr\StaticCall(
                                new Node\Name('Schema'),
                                new Node\Identifier('dropIfExists'),
                                [
                                    new Node\Arg(new Node\Scalar\String_($this->table)),
                                ]
                            )
                        ),
                    ],
                ]);
                $class->stmts = [
                    $upStmt,
                    $downStmt,
                ];
            }
        }

        return $nodes;
    }

    private function getColumnItem(string $name): array
    {
        foreach ($this->tableData->getColumns() as $item) {
            if ($item['column_name'] === $name) {
                return $item;
            }
        }

        throw new InvalidArgumentException('The name of column does not exist.');
    }

    private function isAutoIncrement(Column $column): bool
    {
        $columnItem = $this->getColumnItem($column->getName());
        if ($columnItem['extra'] === 'auto_increment') {
            return true;
        }

        return false;
    }

    private function createMethodCall(Column $column): Node\Expr\MethodCall
    {
        $type = match ($column->getType()) {
            'bigint' => 'bigInteger',
            'int' => 'integer',
            'tinyint' => 'tinyInteger',
            'varchar' => 'string',
            'datetime' => 'dateTime',
            'decimal' => 'decimal',
            'date' => 'date',
            'timestamp' => 'timestamp',
            'json' => 'json',
            'float' => 'float',
            'text' => 'text',
            default => throw new InvalidArgumentException("The type of {$column->getType()} cannot be supported."),
        };
        $extra = [];
        $columnItem = $this->getColumnItem($column->getName());
        if ($this->isAutoIncrement($column)) {
            $extra['autoIncrement'] = true;
        }
        if (str_contains($columnItem['column_type'], 'unsigned')) {
            $extra['unsigned'] = true;
        }
        if ($type === 'string') {
            $extra['length'] = $columnItem['character_maximum_length'];
        }
        if ($type === 'decimal' || $type === 'float') {
            $extra['total'] = $columnItem['numeric_precision'];
            $extra['places'] = $columnItem['numeric_scale'];
        }
        return new Node\Expr\MethodCall(
            new Node\Expr\Variable('table'),
            new Node\Identifier('addColumn'),
            [
                new Node\Arg(new Node\Scalar\String_($type)),
                new Node\Arg(new Node\Scalar\String_($column->getName())),
                PhpParser::getInstance()->getExprFromValue($extra),
            ]
        );
    }

    private function createMethodCallFromNullable(Node\Expr $expr, Column $column)
    {
        if ($column->isNullable()) {
            return new Node\Expr\MethodCall(
                $expr,
                new Node\Identifier('nullable')
            );
        }

        return $expr;
    }

    private function createMethodCallFromDefault(Node\Expr $expr, Column $column)
    {
        if ($column->getDefault() !== null) {
            return new Node\Expr\MethodCall(
                $expr,
                new Node\Identifier('default'),
                [
                    new Node\Arg(new Node\Scalar\String_($column->getDefault())),
                ]
            );
        }

        return $expr;
    }

    private function createMethodCallFromComment(Node\Expr $expr, Column $column)
    {
        if ($column->getComment()) {
            return new Node\Expr\MethodCall(
                $expr,
                new Node\Identifier('comment'),
                [
                    new Node\Arg(new Node\Scalar\String_($column->getComment())),
                ]
            );
        }

        return $expr;
    }

    private function createStmtFromColumn(Column $column)
    {
        $expr = $this->createMethodCall($column);
        $expr = $this->createMethodCallFromNullable($expr, $column);
        $expr = $this->createMethodCallFromDefault($expr, $column);
        $expr = $this->createMethodCallFromComment($expr, $column);

        return new Node\Stmt\Expression($expr);
    }

    private function createStmtFromComment()
    {
        $comment = $this->tableData->getComment();
        if (! $comment) {
            return [];
        }
        return [
            new Node\Stmt\Expression(new Node\Expr\MethodCall(
                new Node\Expr\Variable('table'),
                new Node\Identifier('comment'),
                [
                    new Node\Arg(new Node\Scalar\String_($comment)),
                ]
            )),
        ];
    }

    private function createStmtFromIndexes(bool $isAutoIncrement)
    {
        $indexes = [];
        foreach ($this->tableData->getIndexes() as $index) {
            $indexes[$index['key_name']][] = $index;
        }

        $result = [];
        foreach ($indexes as $keyName => $index) {
            if ($isAutoIncrement && $keyName === 'PRIMARY') {
                continue;
            }

            $isUnique = $index[0]['non_unique'] === 0;
            $isPrimary = $keyName === 'PRIMARY';

            if ($isPrimary) {
                $method = 'primary';
            } elseif ($isUnique) {
                $method = 'unique';
            } else {
                $method = 'index';
            }

            $columns = array_column($index, 'column_name');

            $result[] = new Node\Stmt\Expression(new Node\Expr\MethodCall(
                new Node\Expr\Variable('table'),
                new Node\Identifier($method),
                value(static function () use ($columns, $keyName) {
                    $result = [
                        PhpParser::getInstance()->getExprFromValue($columns),
                    ];
                    if ($keyName !== 'PRIMARY') {
                        $result[] = new Node\Arg(new Node\Scalar\String_($keyName));
                    }
                    return $result;
                })
            ));
        }

        return $result;
    }
}

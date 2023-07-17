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

use Hyperf\CodeParser\PhpDocReader;
use Hyperf\CodeParser\PhpParser;
use Hyperf\Contract\Castable;
use Hyperf\Contract\CastsAttributes;
use Hyperf\Contract\CastsInboundAttributes;
use Hyperf\Database\Commands\ModelOption;
use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Relations\BelongsTo;
use Hyperf\Database\Model\Relations\BelongsToMany;
use Hyperf\Database\Model\Relations\HasMany;
use Hyperf\Database\Model\Relations\HasManyThrough;
use Hyperf\Database\Model\Relations\HasOne;
use Hyperf\Database\Model\Relations\HasOneThrough;
use Hyperf\Database\Model\Relations\MorphMany;
use Hyperf\Database\Model\Relations\MorphOne;
use Hyperf\Database\Model\Relations\MorphTo;
use Hyperf\Database\Model\Relations\MorphToMany;
use Hyperf\Database\Model\Relations\Relation;
use Hyperf\Stringable\Str;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use RuntimeException;

class ModelUpdateVisitor extends NodeVisitorAbstract
{
    public const RELATION_METHODS = [
        'hasMany' => HasMany::class,
        'hasManyThrough' => HasManyThrough::class,
        'hasOneThrough' => HasOneThrough::class,
        'belongsToMany' => BelongsToMany::class,
        'hasOne' => HasOne::class,
        'belongsTo' => BelongsTo::class,
        'morphOne' => MorphOne::class,
        'morphTo' => MorphTo::class,
        'morphMany' => MorphMany::class,
        'morphToMany' => MorphToMany::class,
        'morphedByMany' => MorphToMany::class,
    ];

    protected Model $class;

    /**
     * @var Node\Stmt\ClassMethod[]
     */
    protected array $methods = [];

    protected array $properties = [];

    public function __construct(string $class, protected array $columns, protected ModelOption $option)
    {
        $this->class = new $class();
    }

    public function beforeTraverse(array $nodes)
    {
        $this->methods = PhpParser::getInstance()->getAllMethodsFromStmts($nodes);
        sort($this->methods);

        $this->initPropertiesFromMethods();

        return null;
    }

    public function leaveNode(Node $node)
    {
        switch ($node) {
            case $node instanceof Node\Stmt\PropertyProperty:
                if ((string) $node->name === 'fillable' && $this->option->isRefreshFillable()) {
                    $node = $this->rewriteFillable($node);
                } elseif ((string) $node->name === 'casts') {
                    $node = $this->rewriteCasts($node);
                }
                return $node;
            case $node instanceof Node\Stmt\Class_:
                $node->setDocComment(new Doc($this->parse()));
                return $node;
        }

        return null;
    }

    protected function rewriteFillable(Node\Stmt\PropertyProperty $node): Node\Stmt\PropertyProperty
    {
        $items = [];
        foreach ($this->columns as $column) {
            $items[] = new Node\Expr\ArrayItem(new Node\Scalar\String_($column['column_name']));
        }

        $node->default = new Node\Expr\Array_($items, [
            'kind' => Node\Expr\Array_::KIND_SHORT,
        ]);
        return $node;
    }

    protected function rewriteCasts(Node\Stmt\PropertyProperty $node): Node\Stmt\PropertyProperty
    {
        $items = [];
        $keys = [];
        if ($node->default instanceof Node\Expr\Array_) {
            $items = $node->default->items;
        }

        if ($this->option->isForceCasts()) {
            $items = [];
            $casts = $this->class->getCasts();
            foreach ($node->default->items as $item) {
                $caster = $casts[$item->key->value] ?? null;
                if ($caster && $this->isCaster($caster)) {
                    $items[] = $item;
                }
            }
        }

        foreach ($items as $item) {
            $keys[] = $item->key->value;
        }

        foreach ($this->columns as $column) {
            $name = $column['column_name'];
            $type = $column['cast'] ?? null;
            if (in_array($name, $keys)) {
                continue;
            }
            if ($type || $type = $this->formatDatabaseType($column['data_type'])) {
                $items[] = new Node\Expr\ArrayItem(
                    new Node\Scalar\String_($type),
                    new Node\Scalar\String_($name)
                );
            }
        }

        $node->default = new Node\Expr\Array_($items, [
            'kind' => Node\Expr\Array_::KIND_SHORT,
        ]);
        return $node;
    }

    /**
     * @param object|string $caster
     */
    protected function isCaster($caster): bool
    {
        return is_subclass_of($caster, CastsAttributes::class)
            || is_subclass_of($caster, Castable::class)
            || is_subclass_of($caster, CastsInboundAttributes::class);
    }

    protected function parse(): string
    {
        $doc = '/**' . PHP_EOL;
        $doc = $this->parseProperty($doc);
        if ($this->option->isWithIde()) {
            $doc .= ' * @mixin \\' . GenerateModelIDEVisitor::toIDEClass(get_class($this->class)) . PHP_EOL;
        }
        $doc .= ' */';
        return $doc;
    }

    protected function parseProperty(string $doc): string
    {
        foreach ($this->columns as $column) {
            [$name, $type, $comment] = $this->getProperty($column);
            if (array_key_exists($name, $this->properties)) {
                if (! empty($comment)) {
                    $this->properties[$name]['comment'] = $comment;
                }
                continue;
            }
            $doc .= sprintf(' * @property %s $%s %s', $type, $name, $comment) . PHP_EOL;
        }
        foreach ($this->properties as $name => $property) {
            $comment = $property['comment'] ?? '';
            if ($property['read'] && $property['write']) {
                $doc .= sprintf(' * @property %s $%s %s', $property['type'], $name, $comment) . PHP_EOL;
                continue;
            }
            if ($property['read']) {
                $doc .= sprintf(' * @property-read %s $%s %s', $property['type'], $name, $comment) . PHP_EOL;
                continue;
            }
            if ($property['write']) {
                $doc .= sprintf(' * @property-write %s $%s %s', $property['type'], $name, $comment) . PHP_EOL;
                continue;
            }
        }
        return $doc;
    }

    protected function initPropertiesFromMethods()
    {
        $reflection = new ReflectionClass(get_class($this->class));
        $casts = $this->class->getCasts();

        foreach ($this->methods as $methodStmt) {
            $methodName = $methodStmt->name->name;
            $method = $reflection->getMethod($methodName);
            if (Str::startsWith($method->getName(), 'get') && Str::endsWith($method->getName(), 'Attribute')) {
                // Magic get<name>Attribute
                $name = Str::snake(substr($method->getName(), 3, -9));
                if (! empty($name)) {
                    $type = PhpDocReader::getInstance()->getReturnType($method, true);
                    $this->setProperty($name, $type, true, false, '', false, 1);
                }
                continue;
            }

            if (Str::startsWith($method->getName(), 'set') && Str::endsWith($method->getName(), 'Attribute')) {
                // Magic set<name>Attribute
                $name = Str::snake(substr($method->getName(), 3, -9));
                if (! empty($name)) {
                    $this->setProperty($name, [], false, true, '', false, 1);
                }
                continue;
            }

            if ($method->getNumberOfParameters() > 0) {
                continue;
            }

            $return = end($methodStmt->stmts);
            if ($return instanceof Node\Stmt\Return_) {
                $expr = $return->expr;
                if (
                    $expr instanceof Node\Expr\MethodCall
                    && $expr->name instanceof Node\Identifier
                    && is_string($expr->name->name)
                ) {
                    $loop = 0;
                    while ($expr->var instanceof Node\Expr\MethodCall) {
                        if ($loop > 32) {
                            throw new RuntimeException('max loop reached!');
                        }
                        ++$loop;
                        $expr = $expr->var;
                    }
                    $name = $this->getMethodRelationName($method) ?? $expr->name->name;
                    if (array_key_exists($name, self::RELATION_METHODS)) {
                        if ($name === 'morphTo') {
                            // Model isn't specified because relation is polymorphic
                            $this->setProperty($method->getName(), ['\\' . Model::class], true, false, '', true);
                        } elseif (isset($expr->args[0]) && $expr->args[0]->value instanceof Node\Expr\ClassConstFetch) {
                            $related = $expr->args[0]->value->class->toCodeString();
                            if (str_contains($name, 'Many')) {
                                // Collection or array of models (because Collection is Arrayable)
                                $this->setProperty($method->getName(), [$this->getCollectionClass($related), $related . '[]'], true, false, '', true);
                            } else {
                                // Single model is returned
                                $this->setProperty($method->getName(), [$related], true, false, '', true);
                            }
                        }
                    }
                }
            }
        }

        // The custom caster.
        foreach ($casts as $key => $caster) {
            if (is_subclass_of($caster, Castable::class)) {
                $caster = $caster::castUsing();
            }

            if (is_subclass_of($caster, CastsAttributes::class)) {
                $ref = new ReflectionClass($caster);
                $method = $ref->getMethod('get');
                if ($type = $method->getReturnType()) {
                    // Get return type which defined in `CastsAttributes::get()`.
                    $this->setProperty($key, ['\\' . ltrim($type->getName(), '\\')], true, true, '', true);
                }
            }
        }
    }

    protected function getMethodRelationName(ReflectionMethod $method): ?string
    {
        $returnType = $method->getReturnType();
        if ($returnType instanceof ReflectionNamedType) {
            $array = explode('\\', $returnType->getName());
            return Str::camel(array_pop($array));
        }

        return null;
    }

    protected function setProperty(string $name, array $type = [], bool $read = false, bool $write = false, string $comment = '', bool $nullable = false, int $priority = 0)
    {
        if (! isset($this->properties[$name])) {
            $this->properties[$name] = [];
            $this->properties[$name]['type'] = implode('|', array_unique($type ?: ['mixed']));
            $this->properties[$name]['read'] = false;
            $this->properties[$name]['write'] = false;
            $this->properties[$name]['comment'] = $comment;
            $this->properties[$name]['priority'] = 0;
        }
        if ($this->properties[$name]['priority'] > $priority) {
            return;
        }

        $type = array_merge(explode('|', $this->properties[$name]['type'] ?? []), $type);
        if ($nullable) {
            $type[] = 'null';
        }
        $this->properties[$name]['type'] = implode('|', array_unique($type));
        $this->properties[$name]['read'] = $this->properties[$name]['read'] || $read;
        $this->properties[$name]['write'] = $this->properties[$name]['write'] || $write;
        $this->properties[$name]['priority'] = $priority;
    }

    protected function getProperty($column): array
    {
        $name = $this->option->isCamelCase() ? Str::camel($column['column_name']) : $column['column_name'];

        $type = $this->formatPropertyType($column['data_type'], $column['cast'] ?? null);

        $comment = $this->option->isWithComments() ? $column['column_comment'] ?? '' : '';

        return [$name, $type, $comment];
    }

    protected function formatDatabaseType(string $type): ?string
    {
        return match ($type) {
            'tinyint', 'smallint', 'mediumint', 'int', 'bigint' => 'integer',
            'bool', 'boolean' => 'boolean',
            default => null,
        };
    }

    protected function formatPropertyType(string $type, ?string $cast): ?string
    {
        if (! isset($cast)) {
            $cast = $this->formatDatabaseType($type) ?? 'string';
        }

        return match ($cast) {
            'integer' => 'int',
            'date', 'datetime' => '\Carbon\Carbon',
            'json' => 'array',
            default => $cast,
        };
    }

    protected function getCollectionClass($className): string
    {
        // Return something in the very very unlikely scenario the model doesn't
        // have a newCollection() method.
        if (! method_exists($className, 'newCollection')) {
            return '\\' . Collection::class;
        }

        /** @var Model $model */
        $model = new $className();
        return '\\' . get_class($model->newCollection());
    }
}

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
use Hyperf\Utils\Str;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use RuntimeException;

class ModelUpdateVisitor extends NodeVisitorAbstract
{
    const RELATION_METHODS = [
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

    /**
     * @var Model
     */
    protected $class;

    /**
     * @var array
     */
    protected $columns = [];

    /**
     * @var ModelOption
     */
    protected $option;

    /**
     * @var array
     */
    protected $methods = [];

    /**
     * @var array
     */
    protected $properties = [];

    public function __construct($class, $columns, ModelOption $option)
    {
        $this->class = new $class();
        $this->columns = $columns;
        $this->option = $option;
        $this->initPropertiesFromMethods();
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
        /** @var ReflectionClass $reflection */
        $reflection = BetterReflectionManager::getReflector()->reflect(get_class($this->class));
        $methods = $reflection->getImmediateMethods();
        $namespace = $reflection->getDeclaringNamespaceAst();
        $casts = $this->class->getCasts();

        sort($methods);
        /** @var ReflectionMethod $method */
        foreach ($methods as $method) {
            if (Str::startsWith($method->getName(), 'get') && Str::endsWith($method->getName(), 'Attribute')) {
                // Magic get<name>Attribute
                $name = Str::snake(substr($method->getName(), 3, -9));
                if (! empty($name)) {
                    $type = BetterReflectionManager::getReturnFinder()->__invoke($method, $namespace);
                    if (empty($type) && $returnType = $method->getReturnType()) {
                        $returnTypeName = $returnType->getName();
                        if (class_exists($returnTypeName)) {
                            $returnTypeName = '\\' . $returnTypeName;
                        }
                        $type = [$returnTypeName];
                    }
                    $this->setProperty($name, $type, true, null, '', false, 1);
                }
                continue;
            }

            if (Str::startsWith($method->getName(), 'set') && Str::endsWith($method->getName(), 'Attribute')) {
                // Magic set<name>Attribute
                $name = Str::snake(substr($method->getName(), 3, -9));
                if (! empty($name)) {
                    $this->setProperty($name, null, null, true, '', false, 1);
                }
                continue;
            }

            if ($method->getNumberOfParameters() > 0) {
                continue;
            }

            $return = $method->getReturnStatementsAst();
            // Magic Relation
            if (count($return) === 1 && $return[0] instanceof Node\Stmt\Return_) {
                $expr = $return[0]->expr;
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
                    $name = $expr->name->name;
                    if (array_key_exists($name, self::RELATION_METHODS)) {
                        if ($name === 'morphTo') {
                            // Model isn't specified because relation is polymorphic
                            $this->setProperty($method->getName(), ['\\' . Model::class], true);
                        } elseif (isset($expr->args[0]) && $expr->args[0]->value instanceof Node\Expr\ClassConstFetch) {
                            $related = $expr->args[0]->value->class->toCodeString();
                            if (strpos($name, 'Many') !== false) {
                                // Collection or array of models (because Collection is Arrayable)
                                $this->setProperty($method->getName(), [$this->getCollectionClass($related), $related . '[]'], true);
                            } else {
                                // Single model is returned
                                $this->setProperty($method->getName(), [$related], true);
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
                $ref = BetterReflectionManager::getReflector()->reflect($caster);
                $method = $ref->getMethod('get');
                if ($ast = $method->getReturnStatementsAst()[0]) {
                    if ($ast instanceof Node\Stmt\Return_
                        && $ast->expr instanceof Node\Expr\New_
                        && $ast->expr->class instanceof Node\Name\FullyQualified
                    ) {
                        $this->setProperty($key, [$ast->expr->class->toCodeString()], true, true);
                    } elseif ($type = $method->getReturnType()) {
                        // Get return type which defined in `CastsAttributes::get()`.
                        $this->setProperty($key, ['\\' . ltrim($type->getName(), '\\')], true, true);
                    }
                }
            }
        }
    }

    protected function setProperty(string $name, array $type = null, bool $read = null, bool $write = null, string $comment = '', bool $nullable = false, int $priority = 0)
    {
        if (! isset($this->properties[$name])) {
            $this->properties[$name] = [];
            $this->properties[$name]['type'] = 'mixed';
            $this->properties[$name]['read'] = false;
            $this->properties[$name]['write'] = false;
            $this->properties[$name]['comment'] = (string) $comment;
            $this->properties[$name]['priority'] = 0;
        }
        if ($this->properties[$name]['priority'] > $priority) {
            return;
        }
        if ($type !== null) {
            if ($nullable) {
                $type[] = 'null';
            }
            $this->properties[$name]['type'] = implode('|', array_unique($type));
        }
        if ($read !== null) {
            $this->properties[$name]['read'] = $read;
        }
        if ($write !== null) {
            $this->properties[$name]['write'] = $write;
        }
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
        switch ($type) {
            case 'tinyint':
            case 'smallint':
            case 'mediumint':
            case 'int':
            case 'bigint':
                return 'integer';
            case 'bool':
            case 'boolean':
                return 'boolean';
            default:
                return null;
        }
    }

    protected function formatPropertyType(string $type, ?string $cast): ?string
    {
        if (! isset($cast)) {
            $cast = $this->formatDatabaseType($type) ?? 'string';
        }

        switch ($cast) {
            case 'integer':
                return 'int';
            case 'date':
            case 'datetime':
                return '\Carbon\Carbon';
            case 'json':
                return 'array';
        }

        return $cast;
    }

    protected function getCollectionClass($className): string
    {
        // Return something in the very very unlikely scenario the model doesn't
        // have a newCollection() method.
        if (! method_exists($className, 'newCollection')) {
            return Collection::class;
        }

        /** @var Model $model */
        $model = new $className();
        return '\\' . get_class($model->newCollection());
    }
}

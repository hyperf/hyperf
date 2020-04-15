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

use Barryvdh\Reflection\DocBlock;
use Hyperf\Database\Commands\ModelOption;
use Hyperf\Database\Model\Builder;
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
use Hyperf\Utils\Str;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class ModelUpdateVisitor extends NodeVisitorAbstract
{
    /**
     * @var string
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

    public function __construct($class,$columns, ModelOption $option)
    {
        $this->class = $class;
        $this->columns = $columns;
        $this->option = $option;
        $this->getPropertiesFromMethods(new $this->class);
    }

    public function leaveNode(Node $node)
    {
        switch ($node) {
            case $node instanceof Node\Stmt\PropertyProperty:
                if ((string)$node->name === 'fillable' && $this->option->isRefreshFillable()) {
                    $node = $this->rewriteFillable($node);
                } elseif ((string)$node->name === 'casts') {
                    $node = $this->rewriteCasts($node);
                }
                return $node;
            case $node instanceof Node\Stmt\Class_:
                $node->setDocComment(new Doc($this->parseProperty()));
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
        foreach ($this->columns as $column) {
            $name = $column['column_name'];
            $type = $column['cast'] ?? null;
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

    protected function parseProperty() :string
    {
        $doc = '/**' . PHP_EOL;
        foreach ($this->columns as $column) {
            [$name, $type, $comment] = $this->getProperty($column);
            $doc .= sprintf(' * @property %s $%s %s', $type, $name, $comment) . PHP_EOL;
        }
        foreach ($this->properties as $name => $property) {
            if($property['read'] && $property['write']) {
                $doc .= sprintf(' * @property %s $%s', $property['type'], $name) . PHP_EOL;
                continue;
            }
            if($property['read']) {
                $doc .= sprintf(' * @property-read %s $%s', $property['type'], $name) . PHP_EOL;
                continue;
            }
            if($property['write']) {
                $doc .= sprintf(' * @property-write %s $%s', $property['type'], $name) . PHP_EOL;
                continue;
            }
        }
        $doc .= ' */';
        return $doc;
    }

    /**
     * @author    Barry vd. Heuvel <barryvdh@gmail.com>
     * @copyright 2014 Barry vd. Heuvel / Fruitcake Studio (http://www.fruitcakestudio.nl)
     * @license   http://www.opensource.org/licenses/mit-license.php MIT
     * @link      https://github.com/barryvdh/laravel-ide-helper
     */
    protected function getPropertiesFromMethods($model)
    {
        /** @var Model $model */
        $methods = get_class_methods($model);
        if ($methods) {
            sort($methods);
            foreach ($methods as $method) {
                if (Str::startsWith($method, 'get') && Str::endsWith(
                        $method,
                        'Attribute'
                    ) && $method !== 'getAttribute'
                ) {
                    //Magic get<name>Attribute
                    $name = Str::snake(substr($method, 3, -9));
                    if (!empty($name)) {
                        $reflection = new \ReflectionMethod($model, $method);
                        $type = $this->getReturnTypeFromDocBlock($reflection);
                        $this->setProperty($name, $type, true, null);
                    }
                } elseif (Str::startsWith($method, 'set') && Str::endsWith(
                        $method,
                        'Attribute'
                    ) && $method !== 'setAttribute'
                ) {
                    //Magic set<name>Attribute
                    $name = Str::snake(substr($method, 3, -9));
                    if (!empty($name)) {
                        $this->setProperty($name, null, null, true);
                    }
                } elseif (Str::startsWith($method, 'scope') && $method !== 'scopeQuery') {
                    //Magic set<name>Attribute
                    $name = Str::camel(substr($method, 5));
                    if (!empty($name)) {
                        $reflection = new \ReflectionMethod($model, $method);
                        $args = $this->getParameters($reflection);
                        //Remove the first ($query) argument
                        array_shift($args);
                        $this->setMethod($name, Builder::class .'|\\' . $reflection->class, $args);
                    }
                } elseif (in_array($method, ['query', 'newQuery', 'newModelQuery'])) {
                    $reflection = new \ReflectionClass($model);
                    $builder = get_class($model->newModelQuery());

                    $this->setMethod($method, "\\{$builder}|\\" . $reflection->getName());
                } elseif (!method_exists(Model::class, $method)
                    && !Str::startsWith($method, 'get')
                ) {
                    //Use reflection to inspect the code, based on Illuminate/Support/SerializableClosure.php
                    $reflection = new \ReflectionMethod($model, $method);

                    if ($returnType = $reflection->getReturnType()) {
                        $type = $returnType instanceof \ReflectionNamedType
                            ? $returnType->getName()
                            : (string)$returnType;
                    } else {
                        // php 7.x type or fallback to docblock
                        $type = (string)$this->getReturnTypeFromDocBlock($reflection);
                    }

                    $file = new \SplFileObject($reflection->getFileName());
                    $file->seek($reflection->getStartLine() - 1);

                    $code = '';
                    while ($file->key() < $reflection->getEndLine()) {
                        $code .= $file->current();
                        $file->next();
                    }
                    $code = trim(preg_replace('/\s\s+/', '', $code));
                    $begin = strpos($code, 'function');
                    $code = substr($code, $begin, strrpos($code, '}') - $begin + 1);

                    foreach (array(
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
                             ) as $relation => $impl) {
                        $search = '$this->' . $relation . '(';
                        if (stripos($code, $search) || $impl === (string)$type) {
                            //Resolve the relation's model to a Relation object.
                            $methodReflection = new \ReflectionMethod($model, $method);
                            if ($methodReflection->getNumberOfParameters()) {
                                continue;
                            }

                            // Adding constraints requires reading model properties which
                            // can cause errors. Since we don't need constraints we can
                            // disable them when we fetch the relation to avoid errors.
                            $relationObj = Relation::noConstraints(function () use ($model, $method) {
                                return $model->$method();
                            });

                            if ($relationObj instanceof Relation) {
                                $relatedModel = '\\' . get_class($relationObj->getRelated());

                                $relations = [
                                    'hasManyThrough',
                                    'belongsToMany',
                                    'hasMany',
                                    'morphMany',
                                    'morphToMany',
                                    'morphedByMany',
                                ];
                                if (strpos(get_class($relationObj), 'Many') !== false) {
                                    //Collection or array of models (because Collection is Arrayable)
                                    $this->setProperty(
                                        $method,
                                        $this->getCollectionClass($relatedModel) . '|' . $relatedModel . '[]',
                                        true,
                                        null
                                    );
                                    /*
                                    $this->setProperty(
                                        Str::snake($method) . '_count',
                                        'int|null',
                                        true,
                                        false
                                    );
                                    */
                                } elseif ($relation === "morphTo") {
                                    // Model isn't specified because relation is polymorphic
                                    $this->setProperty(
                                        $method,
                                        '\\' .Model::class,
                                        true,
                                        null
                                    );
                                } else {
                                    //Single model is returned
                                    $this->setProperty(
                                        $method,
                                        $relatedModel,
                                        true,
                                        null,
                                        '',
                                        $this->isRelationForeignKeyNullable($relationObj)
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @author    Barry vd. Heuvel <barryvdh@gmail.com>
     * @copyright 2014 Barry vd. Heuvel / Fruitcake Studio (http://www.fruitcakestudio.nl)
     * @license   http://www.opensource.org/licenses/mit-license.php MIT
     * @link      https://github.com/barryvdh/laravel-ide-helper
     */
    private function isRelationForeignKeyNullable(Relation $relation)
    {
        $reflectionObj = new \ReflectionObject($relation);
        if (!$reflectionObj->hasProperty('foreignKey')) {
            return false;
        }
        $fkProp = $reflectionObj->getProperty('foreignKey');
        $fkProp->setAccessible(true);

        return isset($this->nullableColumns[$fkProp->getValue($relation)]);
    }

    /**
     * @author    Barry vd. Heuvel <barryvdh@gmail.com>
     * @copyright 2014 Barry vd. Heuvel / Fruitcake Studio (http://www.fruitcakestudio.nl)
     * @license   http://www.opensource.org/licenses/mit-license.php MIT
     * @link      https://github.com/barryvdh/laravel-ide-helper
     */
    protected function setProperty($name, $type = null, $read = null, $write = null, $comment = '', $nullable = false)
    {
        if (!isset($this->properties[$name])) {
            $this->properties[$name] = array();
            $this->properties[$name]['type'] = 'mixed';
            $this->properties[$name]['read'] = false;
            $this->properties[$name]['write'] = false;
            $this->properties[$name]['comment'] = (string) $comment;
        }
        if ($type !== null) {
            $newType = $this->getTypeOverride($type);
            if ($nullable) {
                $newType .='|null';
            }
            $this->properties[$name]['type'] = $newType;
        }
        if ($read !== null) {
            $this->properties[$name]['read'] = $read;
        }
        if ($write !== null) {
            $this->properties[$name]['write'] = $write;
        }
    }

    protected function getTypeOverride($type)
    {
        //just for compatibility
        $typeOverrides = config('devtool.model.type_overrides', []);

        return isset($typeOverrides[$type]) ? $typeOverrides[$type] : $type;
    }

    /**
     * @author    Barry vd. Heuvel <barryvdh@gmail.com>
     * @copyright 2014 Barry vd. Heuvel / Fruitcake Studio (http://www.fruitcakestudio.nl)
     * @license   http://www.opensource.org/licenses/mit-license.php MIT
     * @link      https://github.com/barryvdh/laravel-ide-helper
     */
    protected function setMethod($name, $type = '', $arguments = array())
    {
        $methods = array_change_key_case($this->methods, CASE_LOWER);

        if (!isset($methods[strtolower($name)])) {
            $this->methods[$name] = array();
            $this->methods[$name]['type'] = $type;
            $this->methods[$name]['arguments'] = $arguments;
        }
    }

    /**
     * @author    Barry vd. Heuvel <barryvdh@gmail.com>
     * @copyright 2014 Barry vd. Heuvel / Fruitcake Studio (http://www.fruitcakestudio.nl)
     * @license   http://www.opensource.org/licenses/mit-license.php MIT
     * @link      https://github.com/barryvdh/laravel-ide-helper
     */
    public function getParameters(ReflectionMethod $method)
    {
        //Loop through the default values for paremeters, and make the correct output string
        $params = array();
        $paramsWithDefault = array();
        /** @var \ReflectionParameter $param */
        foreach ($method->getParameters() as $param) {
            $paramClass = $param->getClass();
            $paramStr = (!is_null($paramClass) ? '\\' . $paramClass->getName() . ' ' : '') . '$' . $param->getName();
            $params[] = $paramStr;
            if ($param->isOptional() && $param->isDefaultValueAvailable()) {
                $default = $param->getDefaultValue();
                if (is_bool($default)) {
                    $default = $default ? 'true' : 'false';
                } elseif (is_array($default)) {
                    $default = '[]';
                } elseif (is_null($default)) {
                    $default = 'null';
                } elseif (is_int($default)) {
                    //$default = $default;
                } else {
                    $default = "'" . trim($default) . "'";
                }
                $paramStr .= " = $default";
            }
            $paramsWithDefault[] = $paramStr;
        }
        return $paramsWithDefault;
    }

    /**
     * @author    Barry vd. Heuvel <barryvdh@gmail.com>
     * @copyright 2014 Barry vd. Heuvel / Fruitcake Studio (http://www.fruitcakestudio.nl)
     * @license   http://www.opensource.org/licenses/mit-license.php MIT
     * @link      https://github.com/barryvdh/laravel-ide-helper
     */
    private function getCollectionClass($className)
    {
        // Return something in the very very unlikely scenario the model doesn't
        // have a newCollection() method.
        if (!method_exists($className, 'newCollection')) {
            return Collection::class;
        }

        /** @var Model $model */
        $model = new $className;
        return '\\' . get_class($model->newCollection());
    }

    /**
     * @author    Barry vd. Heuvel <barryvdh@gmail.com>
     * @copyright 2014 Barry vd. Heuvel / Fruitcake Studio (http://www.fruitcakestudio.nl)
     * @license   http://www.opensource.org/licenses/mit-license.php MIT
     * @link      https://github.com/barryvdh/laravel-ide-helper
     */
    protected function getReturnTypeFromDocBlock(\ReflectionMethod $reflection)
    {
        $type = null;
        $phpdoc = new DocBlock($reflection);

        if ($phpdoc->hasTag('return')) {
            $type = $phpdoc->getTagsByName('return')[0]->getType();
        }

        return $type;
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
            case 'decimal':
            case 'float':
            case 'double':
            case 'real':
                return 'float';
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
}

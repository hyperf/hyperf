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

namespace Hyperf\GraphQL;

use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;

/**
 * An object type built from the Type annotation.
 */
class TypeAnnotatedObjectType extends MutableObjectType
{
    /**
     * @var string
     */
    private $className;

    public function __construct(string $className, array $config)
    {
        $this->className = $className;

        parent::__construct($config);
    }

    public static function createFromAnnotatedClass(string $typeName, string $className, $annotatedObject, FieldsBuilderFactory $fieldsBuilderFactory, RecursiveTypeMapperInterface $recursiveTypeMapper): self
    {
        return new self($className, [
            'name' => $typeName,
            'fields' => function () use ($annotatedObject, $recursiveTypeMapper, $className, $fieldsBuilderFactory) {
                $parentClass = get_parent_class($className);
                $parentType = null;
                if ($parentClass !== false) {
                    if ($recursiveTypeMapper->canMapClassToType($parentClass)) {
                        $parentType = $recursiveTypeMapper->mapClassToType($parentClass, null);
                    }
                }

                $fieldProvider = $fieldsBuilderFactory->buildFieldsBuilder($recursiveTypeMapper);
                if ($annotatedObject !== null) {
                    $fields = $fieldProvider->getFields($annotatedObject);
                } else {
                    $fields = $fieldProvider->getSelfFields($className);
                }
                if ($parentType !== null) {
                    $finalFields = $parentType->getFields();
                    foreach ($fields as $name => $field) {
                        $finalFields[$name] = $field;
                    }
                    return $finalFields;
                }
                return $fields;
            },
            'interfaces' => function () use ($className, $recursiveTypeMapper) {
                return $recursiveTypeMapper->findInterfaces($className);
            },
        ]);
    }

    public function getMappedClassName(): string
    {
        return $this->className;
    }
}

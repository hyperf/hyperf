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

use GraphQL\Type\Definition\InputObjectType;
use Psr\Container\ContainerInterface;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQLite\Types\ArgumentResolver;

/**
 * This class is in charge of creating Webonyx InputTypes from Factory annotations.
 */
class InputTypeGenerator
{
    /**
     * @var FieldsBuilderFactory
     */
    private $fieldsBuilderFactory;

    /**
     * @var array<string, InputObjectType>
     */
    private $cache = [];

    /**
     * @var ArgumentResolver
     */
    private $argumentResolver;

    /**
     * @var InputTypeUtils
     */
    private $inputTypeUtils;

    public function __construct(
        InputTypeUtils $inputTypeUtils,
        FieldsBuilderFactory $fieldsBuilderFactory,
        ArgumentResolver $argumentResolver
    ) {
        $this->inputTypeUtils = $inputTypeUtils;
        $this->fieldsBuilderFactory = $fieldsBuilderFactory;
        $this->argumentResolver = $argumentResolver;
    }

    public function mapFactoryMethod(string $factory, string $methodName, RecursiveTypeMapperInterface $recursiveTypeMapper, ContainerInterface $container): InputObjectType
    {
        $method = new ReflectionMethod($factory, $methodName);

        if ($method->isStatic()) {
            $object = $factory;
        } else {
            $object = $container->get($factory);
        }

        [$inputName, $className] = $this->inputTypeUtils->getInputTypeNameAndClassName($method);

        if (! isset($this->cache[$inputName])) {
            // TODO: add comment argument.
            $this->cache[$inputName] = new ResolvableInputObjectType($inputName, $this->fieldsBuilderFactory, $recursiveTypeMapper, $object, $methodName, $this->argumentResolver, null);
        }

        return $this->cache[$inputName];
    }
}

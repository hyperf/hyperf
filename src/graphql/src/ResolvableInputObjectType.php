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
namespace Hyperf\GraphQL;

use GraphQL\Type\Definition\InputObjectType;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\GraphQLException;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQLite\Types\ArgumentResolver;
use TheCodingMachine\GraphQLite\Types\ResolvableInputInterface;
use TheCodingMachine\GraphQLite\Types\ResolvableInputObjectType as TheCodingMachineResolvableInputObjectType;

use function get_class;

/**
 * A GraphQL input object that can be resolved using a factory.
 */
class ResolvableInputObjectType extends TheCodingMachineResolvableInputObjectType implements ResolvableInputInterface
{
    /**
     * @var ArgumentResolver
     */
    private $argumentResolver;

    /**
     * @var array<int, object|string>|callable
     */
    private $resolve;

    /**
     * QueryField constructor.
     * @param object|string $factory
     */
    public function __construct(string $name, FieldsBuilderFactory $controllerQueryProviderFactory, RecursiveTypeMapperInterface $recursiveTypeMapper, $factory, string $methodName, ArgumentResolver $argumentResolver, ?string $comment, array $additionalConfig = [])
    {
        $this->argumentResolver = $argumentResolver;
        $this->resolve = [$factory, $methodName];

        $fields = function () use ($controllerQueryProviderFactory, $factory, $methodName, $recursiveTypeMapper) {
            $method = new ReflectionMethod($factory, $methodName);
            $fieldProvider = $controllerQueryProviderFactory->buildFieldsBuilder($recursiveTypeMapper);
            return $fieldProvider->getInputFields($method);
        };

        $config = [
            'name' => $name,
            'fields' => $fields,
        ];
        if ($comment) {
            $config['description'] = $comment;
        }

        $config += $additionalConfig;
        InputObjectType::__construct($config);
    }

    /**
     * @return object
     */
    public function resolve(array $args)
    {
        $toPassArgs = [];
        foreach ($this->getFields() as $name => $field) {
            $type = $field->getType();
            if (isset($args[$name])) {
                $val = $this->argumentResolver->resolve($args[$name], $type);
            } elseif ($field->defaultValueExists()) {
                $val = $field->defaultValue;
            } else {
                throw new GraphQLException("Expected argument '{$name}' was not provided in GraphQL input type '" . $this->name . "' used in factory '" . get_class($this->resolve[0]) . '::' . $this->resolve[1] . "()'");
            }

            $toPassArgs[] = $val;
        }

        $resolve = $this->resolve;

        return $resolve(...$toPassArgs);
    }
}

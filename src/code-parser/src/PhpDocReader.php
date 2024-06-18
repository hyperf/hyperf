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

namespace Hyperf\CodeParser;

use PhpDocReader\AnnotationException;
use PhpDocReader\PhpParser\UseStatementParser;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use Reflector;

/**
 * @see https://github.com/PHP-DI/PhpDocReader
 */
class PhpDocReader
{
    private const PRIMITIVE_TYPES = [
        'bool' => 'bool',
        'boolean' => 'bool',
        'string' => 'string',
        'int' => 'int',
        'integer' => 'int',
        'float' => 'float',
        'double' => 'float',
        'array' => 'array',
        'object' => 'object',
        'callable' => 'callable',
        'resource' => 'resource',
        'mixed' => 'mixed',
        'iterable' => 'iterable',
    ];

    protected static ?PhpDocReader $instance = null;

    private UseStatementParser $parser;

    /**
     * @param bool $ignorePhpDocErrors enable or disable throwing errors when PhpDoc errors occur (when parsing annotations)
     */
    public function __construct(private bool $ignorePhpDocErrors = false)
    {
        $this->parser = new UseStatementParser();
    }

    public static function getInstance(): PhpDocReader
    {
        if (static::$instance) {
            return static::$instance;
        }
        return static::$instance = new static();
    }

    /**
     * Parse the docblock of the property to get the type (class or primitive type) of the param annotation.
     *
     * @throws AnnotationException
     */
    public function getReturnType(ReflectionMethod $method, bool $withoutNamespace = false): array
    {
        return $this->readReturnClass($method, true, $withoutNamespace);
    }

    /**
     * Parse the docblock of the property to get the class of the param annotation.
     *
     * @throws AnnotationException
     */
    public function getReturnClass(ReflectionMethod $method, bool $withoutNamespace = false): array
    {
        return $this->readReturnClass($method, false, $withoutNamespace);
    }

    public function isPrimitiveType(string $type): bool
    {
        return array_key_exists($type, self::PRIMITIVE_TYPES);
    }

    protected function readReturnClass(ReflectionMethod $method, bool $allowPrimitiveTypes, bool $withoutNamespace = false): array
    {
        // Use reflection
        $returnType = $method->getReturnType();
        if ($returnType instanceof ReflectionNamedType) {
            if (! $returnType->isBuiltin() || $allowPrimitiveTypes) {
                return [($returnType->allowsNull() ? '?' : '') . $returnType->getName()];
            }
        }

        $docComment = $method->getDocComment();
        if (! $docComment) {
            return ['mixed'];
        }
        if (preg_match('/@return\s+([^\s]+)\s+/', $docComment, $matches)) {
            [, $type] = $matches;
        } else {
            return ['mixed'];
        }

        $result = [];
        $class = $method->getDeclaringClass();
        $types = explode('|', $type);
        foreach ($types as $type) {
            // Ignore primitive types
            if (isset(self::PRIMITIVE_TYPES[$type])) {
                if ($allowPrimitiveTypes) {
                    $result[] = self::PRIMITIVE_TYPES[$type];
                }
                continue;
            }

            // Ignore types containing special characters ([], <> ...)
            if (! preg_match('/^[a-zA-Z0-9\\\_]+$/', $type)) {
                continue;
            }

            // If the class name is not fully qualified (i.e. doesn't start with a \)
            if ($type[0] !== '\\' && ! $withoutNamespace) {
                // Try to resolve the FQN using the class context
                $resolvedType = $this->tryResolveFqn($type, $class, $method);

                if (! $resolvedType && ! $this->ignorePhpDocErrors) {
                    throw new AnnotationException(sprintf(
                        'The @return annotation for parameter "%s" of %s::%s contains a non existent class "%s". '
                        . 'Did you maybe forget to add a "use" statement for this annotation?',
                        $method,
                        $class->name,
                        $method->name,
                        $type
                    ));
                }

                $type = $resolvedType;
            }

            if (! $this->ignorePhpDocErrors && ! $withoutNamespace && ! $this->classExists($type)) {
                throw new AnnotationException(sprintf(
                    'The @return annotation for parameter "%s" of %s::%s contains a non existent class "%s"',
                    $method,
                    $class->name,
                    $method->name,
                    $type
                ));
            }

            // Remove the leading \ (FQN shouldn't contain it)
            $result[] = is_string($type) ? ltrim($type, '\\') : null;
        }

        return $result;
    }

    /**
     * Attempts to resolve the FQN of the provided $type based on the $class and $member context.
     *
     * @return null|string Fully qualified name of the type, or null if it could not be resolved
     */
    protected function tryResolveFqn(string $type, ReflectionClass $class, Reflector $member): ?string
    {
        $alias = ($pos = strpos($type, '\\')) === false ? $type : substr($type, 0, $pos);
        $loweredAlias = strtolower($alias);

        // Retrieve "use" statements
        $uses = $this->parser->parseUseStatements($class);

        if (isset($uses[$loweredAlias])) {
            // Imported classes
            if ($pos !== false) {
                return $uses[$loweredAlias] . substr($type, $pos);
            }
            return $uses[$loweredAlias];
        }

        if ($this->classExists($class->getNamespaceName() . '\\' . $type)) {
            return $class->getNamespaceName() . '\\' . $type;
        }

        if (isset($uses['__NAMESPACE__']) && $this->classExists($uses['__NAMESPACE__'] . '\\' . $type)) {
            // Class namespace
            return $uses['__NAMESPACE__'] . '\\' . $type;
        }

        if ($this->classExists($type)) {
            // No namespace
            return $type;
        }

        // If all fail, try resolving through related traits
        return $this->tryResolveFqnInTraits($type, $class, $member);
    }

    /**
     * Attempts to resolve the FQN of the provided $type based on the $class and $member context, specifically searching
     * through the traits that are used by the provided $class.
     *
     * @return null|string Fully qualified name of the type, or null if it could not be resolved
     */
    protected function tryResolveFqnInTraits(string $type, ReflectionClass $class, Reflector $member): ?string
    {
        /** @var ReflectionClass[] $traits */
        $traits = [];

        // Get traits for the class and its parents
        while ($class) {
            $traits = array_merge($traits, $class->getTraits());
            $class = $class->getParentClass();
        }

        foreach ($traits as $trait) {
            // Eliminate traits that don't have the property/method/parameter
            if ($member instanceof ReflectionProperty && ! $trait->hasProperty($member->name)) {
                continue;
            }
            if ($member instanceof ReflectionMethod && ! $trait->hasMethod($member->name)) {
                continue;
            }
            if ($member instanceof ReflectionParameter && ! $trait->hasMethod($member->getDeclaringFunction()->name)) {
                continue;
            }

            // Run the resolver again with the ReflectionClass instance for the trait
            $resolvedType = $this->tryResolveFqn($type, $trait, $member);

            if ($resolvedType) {
                return $resolvedType;
            }
        }

        return null;
    }

    protected function classExists(string $class): bool
    {
        return class_exists($class) || interface_exists($class);
    }
}

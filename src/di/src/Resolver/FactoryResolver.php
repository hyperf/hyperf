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
namespace Hyperf\Di\Resolver;

use Hyperf\Di\Definition\DefinitionInterface;
use Hyperf\Di\Definition\FactoryDefinition;
use Hyperf\Di\Exception\InvalidDefinitionException;
use Hyperf\Di\Exception\NotCallableException;
use Psr\Container\ContainerInterface;

class FactoryResolver implements ResolverInterface
{
    public function __construct(private ContainerInterface $container, private ResolverInterface $resolver)
    {
    }

    /**
     * Resolve a factory definition to a value.
     *
     * @param FactoryDefinition $definition object that defines how the value should be obtained
     * @param array $parameters optional parameters to use to build the entry
     * @return mixed value obtained from the definition
     * @throws InvalidDefinitionException if the definition cannot be resolved
     */
    public function resolve(DefinitionInterface $definition, array $parameters = [])
    {
        $callable = null;
        try {
            $callable = $definition->getFactory();
            if (! method_exists($callable, '__invoke')) {
                throw new NotCallableException();
            }
            if (is_string($callable)) {
                $callable = $this->container->get($callable);
            }
            return $callable($this->container, $parameters);
        } catch (NotCallableException $e) {
            // Custom error message to help debugging
            if (is_string($callable) && class_exists($callable) && method_exists($callable, '__invoke')) {
                throw new InvalidDefinitionException(sprintf('Entry "%s" cannot be resolved: factory %s. Invokable classes cannot be automatically resolved if autowiring is disabled on the container, you need to enable autowiring or define the entry manually.', $definition->getName(), $e->getMessage()));
            }

            throw new InvalidDefinitionException(sprintf('Entry "%s" cannot be resolved: factory %s', $definition->getName(), $e->getMessage()));
        }
    }

    /**
     * Check if a definition can be resolved.
     *
     * @param DefinitionInterface $definition object that defines how the value should be obtained
     * @param array $parameters optional parameters to use to build the entry
     */
    public function isResolvable(DefinitionInterface $definition, array $parameters = []): bool
    {
        return true;
    }
}

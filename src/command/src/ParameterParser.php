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

namespace Hyperf\Command;

use Closure;
use Hyperf\Contract\NormalizerInterface;
use Hyperf\Di\ClosureDefinitionCollectorInterface;
use Hyperf\Di\MethodDefinitionCollectorInterface;
use Hyperf\Stringable\Str;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputOption;

class ParameterParser
{
    private NormalizerInterface $normalizer;

    private ?ClosureDefinitionCollectorInterface $closureDefinitionCollector = null;

    private ?MethodDefinitionCollectorInterface $methodDefinitionCollector = null;

    public function __construct(private ContainerInterface $container)
    {
        $this->normalizer = $this->container->get(NormalizerInterface::class);

        if ($this->container->has(ClosureDefinitionCollectorInterface::class)) {
            $this->closureDefinitionCollector = $this->container->get(ClosureDefinitionCollectorInterface::class);
        }

        if ($this->container->has(MethodDefinitionCollectorInterface::class)) {
            $this->methodDefinitionCollector = $this->container->get(MethodDefinitionCollectorInterface::class);
        }
    }

    public function parseClosureParameters(Closure $closure, array $arguments): array
    {
        if (! $this->closureDefinitionCollector) {
            return [];
        }

        $definitions = $this->closureDefinitionCollector->getParameters($closure);

        return $this->getInjections($definitions, 'Closure', $arguments);
    }

    public function parseMethodParameters(string $class, string $method, array $arguments): array
    {
        if (! $this->methodDefinitionCollector) {
            return [];
        }

        $definitions = $this->methodDefinitionCollector->getParameters($class, $method);
        return $this->getInjections($definitions, "{$class}::{$method}", $arguments);
    }

    /**
     * @return InputOption[]
     */
    public function parseClosureOptions(Closure $closure): array
    {
        if (! $this->closureDefinitionCollector) {
            return [];
        }

        $definitions = $this->closureDefinitionCollector->getParameters($closure);

        return $this->extractedOptions($definitions);
    }

    /**
     * @return InputOption[]
     */
    public function parseMethodOptions(string $class, string $method): array
    {
        if (! $this->methodDefinitionCollector) {
            return [];
        }

        $definitions = $this->methodDefinitionCollector->getParameters($class, $method);

        return $this->extractedOptions($definitions);
    }

    public function extractedOptions(array $definitions): array
    {
        $options = [];

        foreach ($definitions as $definition) {
            $type = $definition->getName();
            if (! in_array($type, ['int', 'float', 'string', 'bool'])) {
                continue;
            }
            $name = $definition->getMeta('name');
            $mode = $definition->allowsNull() ? InputOption::VALUE_OPTIONAL : InputOption::VALUE_REQUIRED;
            $default = $definition->getMeta('defaultValue');
            $options[] = new InputOption($name, null, $mode, '', $default, []);
        }

        return $options;
    }

    private function getInjections(array $definitions, string $callableName, array $arguments): array
    {
        $injections = [];

        foreach ($definitions as $pos => $definition) {
            $value = $arguments[$pos] ?? $arguments[$definition->getMeta('name')] ?? $arguments[Str::snake($definition->getMeta('name'), '-')] ?? null;
            if ($value === null) {
                if ($definition->getMeta('defaultValueAvailable')) {
                    $injections[] = $definition->getMeta('defaultValue');
                } elseif ($this->container->has($definition->getName())) {
                    $injections[] = $this->container->get($definition->getName());
                } elseif ($definition->allowsNull()) {
                    $injections[] = null;
                } else {
                    throw new InvalidArgumentException("Parameter '{$definition->getMeta('name')}' "
                        . "of {$callableName} should not be null");
                }
            } else {
                $injections[] = $this->normalizer->denormalize($value, $definition->getName());
            }
        }

        return $injections;
    }
}

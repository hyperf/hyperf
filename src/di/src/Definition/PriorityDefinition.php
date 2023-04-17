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
namespace Hyperf\Di\Definition;

class PriorityDefinition
{
    /**
     * @var array<string, int>
     */
    protected array $dependencies = [];

    public function __construct(string $class, int $priority = 0)
    {
        $this->dependencies[$class] = $priority;
    }

    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    public function merge(PriorityDefinition $factory): static
    {
        foreach ($factory->getDependencies() as $class => $priority) {
            if (isset($this->dependencies[$class]) && $priority <= $this->dependencies[$class]) {
                continue;
            }

            $this->dependencies[$class] = $priority;
        }

        return $this;
    }

    public function getDefinition(): string
    {
        $dependencies = array_flip($this->dependencies);
        ksort($dependencies);
        return array_pop($dependencies);
    }
}

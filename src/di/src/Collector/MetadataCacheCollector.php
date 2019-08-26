<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Di\Collector;

use Hyperf\Di\Exception\InvalidDefinitionException;
use Hyperf\Di\Exception\NotFoundException;
use Psr\Container\ContainerInterface;

class MetadataCacheCollector
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $collectors;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function addCollector(string $collector)
    {
        $this->collectors = array_unique(array_merge(
            $this->collectors,
            [$collector]
        ));
    }

    public function serialize(): string
    {
        $metadata = [];
        foreach ($this->collectors as $collector) {
            if ($this->container->has($collector)) {
                $class = $this->container->get($collector);
                if ($class instanceof MetadataCollectorInterface) {
                    $metadata[$collector] = $class->getMetadata();
                }
            }
        }

        return serialize($metadata);
    }

    public function unserialize($serialized): void
    {
        $metadatas = unserialize($serialized);

        foreach ($metadatas as $collector => $metadata) {
            if (! $this->container->has($collector)) {
                throw new NotFoundException(sprintf('Collector %s not found in Container.'));
            }

            $class = $this->container->get($collector);
            if (! $class instanceof MetadataCollectorInterface) {
                throw new InvalidDefinitionException(sprintf('Collector %s is not instanceof MetadataCollectorInterface.'));
            }

            $class->setMetadata($metadata);
        }
    }
}

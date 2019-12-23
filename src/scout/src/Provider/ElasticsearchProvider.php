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

namespace Hyperf\Scout\Provider;

use Hyperf\Contract\ContainerInterface;
use Hyperf\Elasticsearch\ClientBuilderFactory;
use Hyperf\Scout\Engine\ElasticsearchEngine;
use Hyperf\Scout\Engine\Engine;

class ElasticsearchProvider implements ProviderInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function make(string $name): Engine
    {
        $config = $this->container->get(ConfigInterface::class);
        $builder = $this->container->get(ClientBuilderFactory::class)->create();
        $client = $builder->setHosts($config->get("scout.{$name}.hosts"))->build();
        $index = $config->get("scout.{$name}.index");
        return new ElasticsearchEngine($client, $index);
    }
}

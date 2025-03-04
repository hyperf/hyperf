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

namespace Hyperf\Elasticsearch;

use Elastic\Elasticsearch\ClientBuilder;
use Hyperf\Guzzle\ClientFactory as GuzzleClientFactory;
use Psr\Container\ContainerInterface;

class ClientBuilderFactory
{
    protected ?GuzzleClientFactory $guzzleClientFactory = null;

    public function __construct(protected ContainerInterface $container)
    {
        if ($container->has(GuzzleClientFactory::class)) {
            $this->guzzleClientFactory = $container->get(GuzzleClientFactory::class);
        }
    }

    public function create(): ClientBuilder
    {
        $builder = ClientBuilder::create();

        $this->guzzleClientFactory && $builder->setHttpClient(
            $this->guzzleClientFactory->create()
        );

        return $builder;
    }
}

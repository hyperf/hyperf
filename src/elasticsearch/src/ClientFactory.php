<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Elasticsearch;

use Elasticsearch\ClientBuilder;
use Hyperf\Guzzle\RingPHP\CoroutineHandler;
use Psr\Container\ContainerInterface;
use Swoole\Coroutine;

class ClientFactory
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function builder()
    {
        if (method_exists($this->container, 'make')) {
            // Create by DI for AOP.
            $builder = $this->container->make(ClientBuilder::class);
        } else {
            $builder = ClientBuilder::create();
        }

        if (Coroutine::getCid() > 0) {
            $builder->setHandler(new CoroutineHandler());
        }

        return $builder;
    }
}

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
namespace Hyperf\Guzzle;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
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

    public function create(array $options = []): Client
    {
        $stack = null;
        if (Coroutine::getCid() > 0) {
            $stack = HandlerStack::create(new CoroutineHandler());
        }

        $config = array_replace(['handler' => $stack], $options);

        if (method_exists($this->container, 'make')) {
            // Create by DI for AOP.
            return $this->container->make(Client::class, ['config' => $config]);
        }
        return new Client($config);
    }
}

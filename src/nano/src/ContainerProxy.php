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
namespace Hyperf\Nano;

use Hyperf\Contract\ContainerInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

class ContainerProxy implements BoundInterface, ContainerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ResponseInterface
     */
    private $response;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->request = $container->get(RequestInterface::class);
        $this->response = $container->get(ResponseInterface::class);
    }

    public function __call($name, $arguments)
    {
        return $this->container->{$name}(...$arguments);
    }

    public function get($id)
    {
        return $this->container->get($id);
    }

    public function define(string $name, $definition)
    {
        return $this->container->define($name, $definition);
    }

    public function has($id)
    {
        return $this->container->has($id);
    }

    public function make(string $name, array $parameters = [])
    {
        return $this->container->make($name, $parameters);
    }

    public function set(string $name, $entry)
    {
        return $this->container->set($name, $entry);
    }
}

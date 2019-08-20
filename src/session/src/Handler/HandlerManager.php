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

namespace Hyperf\Session\Handler;

use Psr\Container\ContainerInterface;
use SessionHandlerInterface;

class HandlerManager
{
    /**
     * The default session handlers.
     *
     * @var array
     */
    protected $handlers = [
        'redis' => RedisHandler::class,
        'file' => FileHandler::class,
        'null' => NullHandler::class,
    ];

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @throws \InvalidArgumentException when the handler does not exists, or is not implement SessionHandlerInterface
     */
    public function register(string $name, string $handler): void
    {
        if (! class_exists($handler) && ! interface_exists($handler)) {
            throw new \InvalidArgumentException('Invalid session handler.');
        }
        $instance = $this->container->get($handler);
        if (! $instance instanceof SessionHandlerInterface) {
            throw new \InvalidArgumentException('Invalid session handler.');
        }
        $this->handlers[$name] = $handler;
    }

    public function getHandler(string $name): SessionHandlerInterface
    {
        $handler = $this->handlers[$name] ?? '';
        if (! isset($this->handlers[$name]) || ! $this->container->has($handler)) {
            throw new \InvalidArgumentException(sprintf('The handler %s does not exists.', $name));
        }
        return $this->container->get($handler);
    }
}

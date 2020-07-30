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
namespace Hyperf\SuperGlobals\Listener;

use Hyperf\Contract\ContainerInterface;
use Hyperf\Contract\SessionInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\AfterWorkerStart;
use Hyperf\SuperGlobals\Proxy;

class SuperGlobalsInitializeListener implements ListenerInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function listen(): array
    {
        return [
            AfterWorkerStart::class,
        ];
    }

    /**
     * @param AfterWorkerStart $event
     */
    public function process(object $event)
    {
        $_COOKIE = make(Proxy\Cookie::class);
        $_FILES = make(Proxy\File::class);
        $_GET = make(Proxy\Get::class);
        $_POST = make(Proxy\Post::class);
        $_REQUEST = make(Proxy\Request::class);
        $_SERVER = make(Proxy\Server::class, [$_SERVER]);
        $_SESSION = [];
        if ($this->container->has(SessionInterface::class)) {
            $_SESSION = make(Proxy\Session::class);
        }
    }
}

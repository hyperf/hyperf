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
namespace Hyperf\SocketIOServer\SidProvider;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Contract\SessionInterface;
use Hyperf\Session\Session;
use Hyperf\SocketIOServer\SocketIO;
use SessionHandlerInterface;

class SessionSidProvider implements SidProviderInterface
{
    /**
     * @var \Hyperf\Contract\SessionInterface
     */
    private $session;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $container->get(ConfigInterface::class);
        $this->session = $container->get(SessionInterface::class);
    }

    public function getSid(int $fd): string
    {
        $this->session->set('fd', $fd);
        $this->session->set('server', SocketIO::$serverId);
        return $this->session->getId();
    }

    public function isLocal(string $sid): bool
    {
        $session = $this->getSession($sid);
        return $session->get('server') === SocketIO::$serverId;
    }

    public function getFd(string $sid): int
    {
        $session = $this->getSession($sid);
        return (int) $session->get('fd');
    }

    protected function getSession(string $sid): SessionInterface
    {
        return new Session($this->getSessionName(), $this->buildSessionHandler(), $sid);
    }

    protected function getSessionName(): string
    {
        return $this->config->get('session.options.session_name', 'HYPERF_SESSION_ID');
    }

    protected function buildSessionHandler(): SessionHandlerInterface
    {
        $handler = $this->config->get('session.handler');
        if (! $handler || ! class_exists($handler)) {
            throw new \InvalidArgumentException('Invalid handler of session');
        }
        return $this->container->get($handler);
    }
}

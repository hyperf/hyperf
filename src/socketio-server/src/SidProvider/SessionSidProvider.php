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

namespace Hyperf\SocketIOServer\SidProvider;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\SessionInterface;
use Hyperf\Session\Session;
use Hyperf\SocketIOServer\SocketIO;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use SessionHandlerInterface;

class SessionSidProvider implements SidProviderInterface
{
    private SessionInterface $session;

    private ConfigInterface $config;

    public function __construct(private ContainerInterface $container)
    {
        $this->config = $container->get(ConfigInterface::class);
        $this->session = $container->get(SessionInterface::class);
    }

    public function getSid(int $fd): string
    {
        if ($fd === -1 || $fd === 0) {
            return '';
        }
        $this->session->set('fd', $fd);
        $this->session->set('server', SocketIO::$serverId);
        $this->session->save();
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
        $session = new Session($this->getSessionName(), $this->buildSessionHandler(), $sid);
        $session->start();
        return $session;
    }

    protected function getSessionName(): string
    {
        return $this->config->get('session.options.session_name', 'HYPERF_SESSION_ID');
    }

    protected function buildSessionHandler(): SessionHandlerInterface
    {
        $handler = $this->config->get('session.handler');
        if (! $handler || ! class_exists($handler)) {
            throw new InvalidArgumentException('Invalid handler of session');
        }
        return $this->container->get($handler);
    }
}

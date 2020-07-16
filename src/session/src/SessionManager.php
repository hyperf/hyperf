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
namespace Hyperf\Session;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\SessionInterface;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use SessionHandlerInterface;

class SessionManager
{
    /**
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * @var ConfigInterface
     */
    protected $config;

    public function __construct(ContainerInterface $container, ConfigInterface $config)
    {
        $this->container = $container;
        $this->config = $config;
    }

    public function getSessionName(): string
    {
        return $this->config->get('session.options.session_name', 'HYPERF_SESSION_ID');
    }

    public function start(ServerRequestInterface $request): SessionInterface
    {
        $sessionId = $this->parseSessionId($request);
        // @TODO Use make() function to create Session object.
        $session = new Session($this->getSessionName(), $this->buildSessionHandler(), $sessionId);
        if (! $session->start()) {
            throw new \RuntimeException('Start session failed.');
        }
        $this->setSession($session);
        return $session;
    }

    public function end(SessionInterface $session): void
    {
        $session->save();
    }

    public function getSession(): SessionInterface
    {
        return Context::get(SessionInterface::class);
    }

    public function setSession(SessionInterface $session): self
    {
        Context::set(SessionInterface::class, $session);
        return $this;
    }

    protected function parseSessionId(ServerRequestInterface $request): ?string
    {
        $cookies = $request->getCookieParams();
        foreach ($cookies as $key => $value) {
            if ($key === $this->getSessionName()) {
                return (string) $value;
            }
        }
        return null;
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

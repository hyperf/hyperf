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

namespace Hyperf\Session;

use Hyperf\Contract\SessionInterface;
use Hyperf\Session\Handler\HandlerManager;
use Hyperf\Utils\Context;
use Psr\Http\Message\ServerRequestInterface;

class SessionManager
{
    /**
     * @var HandlerManager
     */
    private $handlerManager;

    public function __construct(HandlerManager $handlerManager)
    {
        $this->handlerManager = $handlerManager;
    }

    public function getSessionName(): string
    {
        return 'HYPERF_SESSION_ID';
    }

    public function start(ServerRequestInterface $request): SessionInterface
    {
        $sessionId = $this->parseSessionId($request);
        $handler = 'file';
        $session = new Session($this->getSessionName(), $this->handlerManager->getHandler($handler), $sessionId);
        $session->start();
        $this->setSession($session);
        return $session;
    }

    public function end(SessionInterface $session)
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
}

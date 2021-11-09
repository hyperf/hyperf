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
namespace Hyperf\SocketIOServer\Aspect;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Session\SessionManager;
use Hyperf\WebSocketServer\Context;
use Psr\Http\Message\ServerRequestInterface;

class SessionAspect extends AbstractAspect
{
    public array $classes = [
        'Hyperf\SocketIOServer\SocketIO::onClose',
        'Hyperf\SocketIOServer\SocketIO::onOpen',
        'Hyperf\SocketIOServer\SocketIO::onMessage',
    ];

    public function __construct(private SessionManager $sessionManager, private ConfigInterface $config)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! $this->isSessionAvailable()) {
            return $proceedingJoinPoint->process();
        }
        $request = Context::get(ServerRequestInterface::class);
        $session = $this->sessionManager->start($request);
        defer(function () use ($session) {
            $this->sessionManager->end($session);
        });
        return $proceedingJoinPoint->process();
    }

    private function isSessionAvailable(): bool
    {
        return $this->config->has('session.handler');
    }
}

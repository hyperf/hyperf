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
namespace Hyperf\Session\Listener;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\HttpServer\Event\OnRequestStart;
use Hyperf\Session\SessionManager;

/**
 * @Listener
 */
class SessionStart implements ListenerInterface
{
    /**
     * @var SessionManager
     */
    protected $sessionManager;

    /**
     * @var ConfigInterface
     */
    protected $config;

    public function __construct(SessionManager $sessionManager, ConfigInterface $config)
    {
        $this->sessionManager = $sessionManager;
        $this->config = $config;
    }

    public function listen(): array
    {
        return [
            OnRequestStart::class,
        ];
    }

    public function process(object $event)
    {
        if ($this->isSessionAvailable()) {
            /* @var OnRequestStart $event */
            $this->sessionManager->start($event->request);
        }
    }

    protected function isSessionAvailable(): bool
    {
        return $this->config->has('session.handler');
    }
}

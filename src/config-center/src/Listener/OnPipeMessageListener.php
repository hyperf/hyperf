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

namespace Hyperf\ConfigCenter\Listener;

use Hyperf\ConfigCenter\Contract\DriverInterface;
use Hyperf\ConfigCenter\Contract\PipeMessageInterface;
use Hyperf\ConfigCenter\DriverFactory;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\OnPipeMessage;
use Hyperf\Process\Event\PipeMessage as UserProcessPipeMessage;

class OnPipeMessageListener implements ListenerInterface
{
    public function __construct(
        protected DriverFactory $driverFactory,
        protected ConfigInterface $config,
        protected StdoutLoggerInterface $logger
    ) {
    }

    /**
     * @return string[] returns the events that you want to listen
     */
    public function listen(): array
    {
        return [
            OnPipeMessage::class,
            UserProcessPipeMessage::class,
        ];
    }

    /**
     * Handle the Event when the event is triggered, all listeners will
     * complete before the event is returned to the EventDispatcher.
     */
    public function process(object $event): void
    {
        if ($instance = $this->createDriverInstance()) {
            if ($event instanceof OnPipeMessage || $event instanceof UserProcessPipeMessage) {
                $event->data instanceof PipeMessageInterface && $instance->onPipeMessage($event->data);
            }
        }
    }

    protected function createDriverInstance(): ?DriverInterface
    {
        if (! $this->config->get('config_center.enable', false)) {
            return null;
        }

        $driver = $this->config->get('config_center.driver', '');
        if (! $driver) {
            return null;
        }
        return $this->driverFactory->create($driver);
    }
}

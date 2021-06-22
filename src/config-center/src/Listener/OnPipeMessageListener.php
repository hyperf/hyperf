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
use Hyperf\ConfigCenter\DriverFactory;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\OnPipeMessage;
use Hyperf\Process\Event\PipeMessage as UserProcessPipMessage;

class OnPipeMessageListener implements ListenerInterface
{
    /**
     * @var \Hyperf\ConfigCenter\DriverFactory
     */
    protected $driverFactory;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(DriverFactory $driverFactory, ConfigInterface $config, StdoutLoggerInterface $logger)
    {
        $this->driverFactory = $driverFactory;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @return string[] returns the events that you want to listen
     */
    public function listen(): array
    {
        return [
            OnPipeMessage::class,
            UserProcessPipMessage::class,
        ];
    }

    /**
     * Handle the Event when the event is triggered, all listeners will
     * complete before the event is returned to the EventDispatcher.
     */
    public function process(object $event)
    {
        $instance = $this->createDriverInstance();
        $instance && $instance->onPipeMessageHandle($event);
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
        $instance = $this->driverFactory->create($driver);
        if (method_exists($instance, 'setConfig')) {
            $instance->setConfig($this->config);
        }
        return $instance;
    }
}

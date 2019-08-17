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

namespace Hyperf\WebSocketServer\Listener;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\Framework\Event\OnPipeMessage;
use Hyperf\Server\ServerFactory;
use Hyperf\WebSocketServer\Sender;
use Hyperf\WebSocketServer\SenderPipeMessage;
use Psr\Container\ContainerInterface;
use Swoole\Server;

class OnPipeMessageListener implements ListenerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var StdoutLoggerInterface
     */
    private $logger;

    public function __construct(ContainerInterface $container, ConfigInterface $config, StdoutLoggerInterface $logger)
    {
        $this->container = $container;
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
        ];
    }

    /**
     * Handle the Event when the event is triggered, all listeners will
     * complete before the event is returned to the EventDispatcher.
     */
    public function process(object $event)
    {
        if ($event instanceof OnPipeMessage && $event->data instanceof SenderPipeMessage) {
            /** @var SenderPipeMessage $message */
            $message = $event->data;

            try {
                $sender = $this->container->get(Sender::class);

                $sender->proxy($message->name, $message->arguments);
            } catch (\Throwable $exception) {
                $formatter = $this->container->get(FormatterInterface::class);
                $this->logger->warning($formatter->format($exception));
            }
        }
    }

    protected function getServer(): Server
    {
        return $this->container->get(ServerFactory::class)->getServer()->getServer();
    }
}

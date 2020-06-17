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
namespace Hyperf\WebSocketServer\Listener;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\Framework\Event\OnPipeMessage;
use Hyperf\WebSocketServer\Sender;
use Hyperf\WebSocketServer\SenderPipeMessage;
use Psr\Container\ContainerInterface;

class OnPipeMessageListener implements ListenerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @var Sender
     */
    private $sender;

    public function __construct(ContainerInterface $container, StdoutLoggerInterface $logger, Sender $sender)
    {
        $this->container = $container;
        $this->logger = $logger;
        $this->sender = $sender;
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
                $fd = $this->sender->getFdFromProxyMethod($message->name, $message->arguments);
                $this->sender->proxy($fd, $message->arguments);
            } catch (\Throwable $exception) {
                $formatter = $this->container->get(FormatterInterface::class);
                $this->logger->warning($formatter->format($exception));
            }
        }
    }
}

<?php

namespace Hyperf\HttpServer\Listener;


use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\AfterWorkerStart;

/**
 * @Listener()
 */
class AfterWorkerStartListener implements ListenerInterface
{

    /**
     * @var StdoutLoggerInterface
     */
    private $logger;

    public function __construct(StdoutLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return string[] returns the events that you want to listen
     */
    public function listen(): array
    {
        return [
            AfterWorkerStart::class,
        ];
    }

    /**
     * Handle the Event when the event is triggered, all listeners will
     * complete before the event is returned to the EventDispatcher.
     */
    public function process(object $event)
    {
        /** @var AfterWorkerStart $event */
        if ($event->workerId === 0) {
            $server = $event->server;
            $listen = $server->host . ':' . $server->port;
            $this->logger->info(sprintf('HTTP Server %s started', $listen));
        }
    }
}
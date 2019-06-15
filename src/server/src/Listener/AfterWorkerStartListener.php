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

namespace Hyperf\Server\Listener;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\AfterWorkerStart;
use Hyperf\Server\Server;
use Hyperf\Server\ServerManager;

/**
 * @Listener
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
        if (class_exists(AfterWorkerStart::class)) {
            return [
                AfterWorkerStart::class,
            ];
        }
        return [];
    }

    /**
     * Handle the Event when the event is triggered, all listeners will
     * complete before the event is returned to the EventDispatcher.
     */
    public function process(object $event)
    {
        /** @var AfterWorkerStart $event */
        if ($event->workerId === 0) {
            foreach (ServerManager::list() as $name => [$type, $server]) {
                $listen = $server->host . ':' . $server->port;
                $type = value(function () use ($type) {
                    switch ($type) {
                        case Server::SERVER_TCP:
                            return 'TCP';
                            break;
                        case Server::SERVER_WEBSOCKET:
                            return 'WebSocket';
                            break;
                        case Server::SERVER_HTTP:
                        default:
                            return 'HTTP';
                            break;
                    }
                });
                $this->logger->info(sprintf('%s Server listening at %s', $type, $listen));
            }
        }
    }
}

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

namespace Hyperf\Server\Listener;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\AfterWorkerStart;
use Hyperf\Server\Server;
use Hyperf\Server\ServerManager;
use Swoole\Server\Port;

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
            /** @var Port $server */
            foreach (ServerManager::list() as $name => [$type, $server]) {
                $listen = $server->host . ':' . $server->port;
                $type = value(function () use ($type) {
                    switch ($type) {
                        case Server::SERVER_BASE:
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
